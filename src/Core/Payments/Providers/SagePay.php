<?php

namespace GetCandy\Api\Core\Payments\Providers;

use Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GetCandy\Api\Core\Payments\PaymentResponse;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Core\Payments\ThreeDSecureResponse;
use GetCandy\Api\Core\Payments\Models\ReusablePayment;
use GetCandy\Api\Core\Payments\Events\PaymentFailedEvent;
use GetCandy\Api\Core\Payments\Events\PaymentAttemptedEvent;
use GetCandy\Api\Core\Payments\Events\TransactionFetchedEvent;
use GetCandy\Api\Core\Payments\Events\ThreeDSecureAttemptEvent;

class SagePay extends AbstractProvider
{
    /**
     * The SagePay host URL
     *
     * @var string
     */
    protected $host;

    /**
     * When the token expires
     *
     * @var string
     */
    protected $tokenExpires;

    /**
     * The Http client
     *
     * @var Client
     */
    protected $http;

    /**
     * Whether the payment should be deferred
     *
     * @var boolean
     */
    protected $shouldDefer;

    /**
     * Whether payments should auto release
     *
     * @var boolean
     */
    protected $autoRelease;

    public function __construct()
    {
        $this->shouldDefer = (bool) config('getcandy.payments.deferred', false);
        $this->autoRelease = (bool) config('getcandy.payments.auto_release', true);
        $this->http = new Client([
            'base_uri' => config('services.sagepay.host', 'https://pi-test.sagepay.com/api/v1/'),
            'headers' => [
                'Authorization' => 'Basic '.$this->getCredentials(),
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ]
        ]);
    }

    /**
     * Get the name of the provider
     *
     * @return string
     */
    public function getName()
    {
        return 'SagePay';
    }

    /**
     * Validate our token
     *
     * @param string $token
     * @return boolean
     */
    public function validate($token)
    {
        // SagePay doesn't seem to have a good way to
        // check this, so we just return true.
        return true;
    }

    /**
     * Refund a payment
     *
     * @param string $token
     * @param int $amount
     * @param string $description
     * @return Transaction
     */
    public function refund($token, $amount, $description)
    {
        try {
            $response = $this->http->post('transactions', [
                'json' => [
                    'transactionType' => 'Refund',
                    'amount' => $amount,
                    'referenceTransactionId' => $token,
                    'currency' => $this->order->currency, // Get currency from order.
                    'vendorTxCode' => str_random(40),
                    'description' => $description,
                ],
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            $response = new PaymentResponse(false, 'Refund Failed', $errors);
            return $this->createFailedTransaction($errors, $amount, $description);
        }

        $content = json_decode($response->getBody()->getContents(), true);
        return $this->createRefundTransaction($content, $amount, $description);
    }

    /**
     * Make a payment
     *
     * @return PaymentResponse
     */
    public function charge()
    {
        try {
            $payload = [
                'transactionType' => $this->shouldDefer ? 'Deferred' : 'Payment',
                'paymentMethod' => [
                    'card' => [
                        'merchantSessionKey' => $this->fields['merchant_key'],
                        'cardIdentifier' => $this->token,
                    ],
                ],
                'amount' => $this->order->order_total,
                'currency' => $this->order->currency,
                'description' => 'Website Transaction',
                'apply3DSecure' => 'UseMSPSetting',
                'customerFirstName' => $this->order->billing_firstname,
                'customerLastName' => $this->order->billing_lastname,
                'vendorTxCode' => str_random(40),
                'billingAddress' => [
                    'address1' => $this->order->billing_address,
                    'city' => $this->order->billing_city,
                    'postalCode' => $this->order->billing_zip,
                    'country' => 'GB',
                ],
                'entryMethod' => 'Ecommerce',
            ];

            if (! empty($this->fields['save'])) {
                $payload['paymentMethod']['card']['save'] = true;
            }

            if (! empty($this->fields['reusable'])) {
                $payload['paymentMethod']['card']['reusable'] = true;
            }

            $response = $this->http->post('transactions', [
                'json' => $payload,
            ]);

            $content = json_decode($response->getBody()->getContents(), true);

            event(new PaymentAttemptedEvent($content));
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            $response = new PaymentResponse(false, 'Payment Failed', $errors);
            $response->transaction(
                $this->createFailedTransaction($errors)
            );
            return $response;
        }

        // If it's 3DSecured then we return the relevant response
        if ($content['status'] == '3DAuth') {
            return (new ThreeDSecureResponse)
                ->setStatus($content['statusCode'])
                ->setTransactionId($content['transactionId'])
                ->setPaRequest($content['paReq'])
                ->setRedirect($content['acsUrl']);
        } elseif ($content['status'] != 'Ok') {
            $response = new PaymentResponse(false, $content['statusDetail'] ?? 'Rejected', $content);
            return $response->transaction(
                $this->createFailedTransaction($content)
            );
        }

        $response = new PaymentResponse(true, 'Payment Received');

        if (! empty($content['paymentMethod']['card']['reusable'])) {
            $this->saveCard($content['paymentMethod']['card']);
        }

        $response->transaction(
            $this->createSuccessTransaction($content)
        );

        if ($this->autoRelease) {
            $this->releaseTransaction($content['transactionId']);
        }

        return $response;
    }

    /**
     * Save a card for later use
     *
     * @param array $details
     * @return void
     */
    protected function saveCard($details)
    {
        $userId = $this->order->user_id;

        // Delete one if it exists.
        ReusablePayment::where('last_four', '=', $details['lastFourDigits'])
                    ->where('user_id', '=', $userId)->delete();

        $payment = new ReusablePayment;
        $payment->type = strtolower($details['cardType']);
        $payment->provider = 'sagepay';
        $payment->last_four = $details['lastFourDigits'];
        $payment->expires_at = \Carbon\Carbon::createFromFormat('my', $details['expiryDate'])->endOfMonth();
        $payment->token = $details['cardIdentifier'];
        $payment->user_id = $this->order->user_id;
        $payment->save();
    }

    /**
     * Process a ThreeD secure transaction
     *
     * @param string $transactionId
     * @param string $paRes
     * @return Transaction
     */
    public function processThreeD($transactionId, $paRes)
    {
        try {
            $response = $this->http->post('transactions/'.$transactionId.'/3d-secure', [
                'json' => ['paRes' => $paRes],
            ]);
            $content = json_decode($response->getBody()->getContents(), true);
            event(new ThreeDSecureAttemptEvent($content));
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $this->createFailedTransaction([
                'statusDetail' => $errors['description'],
                'status' => 'failed',
                'transactionId' => $transactionId,
            ]);
        }

        // We are authenticated, so lets get the transaction from the API
        $transaction = $this->getTransactionFromApi($transactionId);

        if ($transaction['status'] != 'Ok') {
            $candyTransaction = $this->createFailedTransaction($transaction);
            $this->markTransactionToVoid($transaction['transactionId']);
            return $candyTransaction;
        }

        if (! empty($transaction['paymentMethod']['card']['reusable'])) {
            $this->saveCard($transaction['paymentMethod']['card']);
        }

        $candyTransaction = $this->createSuccessTransaction($transaction);

        if ($this->autoRelease) {
            $this->releaseTransaction($transaction['transactionId']);
        }

        return $candyTransaction;
    }

    /**
     * Marks a transaction to be voided
     *
     * @param string $transationId
     * @return void
     */
    protected function markTransactionToVoid($transactionId)
    {
        $transaction = Transaction::where('transaction_id', '=', $transactionId)->first();
        $transaction->should_void = true;
        $transaction->save();
    }

    /**
     * Get a transaction from the API
     *
     * @param string $id
     * @param integer $attempt
     * @return array
     */
    public function getTransactionFromApi($id, $attempt = 1)
    {
        try {
            $response = $this->http->get('transactions/'.$id);
            $content = json_decode($response->getBody()->getContents(), true);
            event(new TransactionFetchedEvent($content));
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            event(new TransactionFetchedEvent($errors));
            if ($attempt > 4) {
                return [
                    'transactionId' => $id,
                    'status' => $errors['code'],
                    'statusDetail' => $errors['description'],
                ];
            }
            $attempt++;
            sleep(1);
            return $this->getTransactionFromApi($id, $attempt);
        }
        return $content;
    }

    /**
     * Void a transaction
     *
     * @param string $transactionId
     * @param string $reason
     * @return void
     */
    public function voidTransaction($transactionId, $reason = null)
    {
        $transaction = Transaction::where('transaction_id', '=', $transactionId)->first();
        try {
            $this->http->post('transactions/'.$transactionId.'/instructions', [
                'json' => [
                    'instructionType' => 'void',
                ],
            ]);
            $transaction->voided_at = now();
            $transaction->voided_reason = $reason;
            $transaction->save();
            return $transaction;
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return [
                'transactionId' => $transactionId,
                'status' => $errors['code'],
                'statusDetail' => $errors['description'],
            ];
        }
    }

    /**
     * Abort the transaction
     *
     * @param string $transactionId
     * @return Transaction
     */
    public function abortTransaction($transactionId)
    {
        $transaction = Transaction::where('transaction_id', '=', $transactionId)->first();
        try {
            $this->http->post('transactions/'.$transactionId.'/instructions', [
                'json' => [
                    'instructionType' => 'abort',
                ],
            ]);
            $transaction->voided_at = now();
            $transaction->voided_reason = 'Abort Payment Release';
            $transaction->status = 'Aborted';
            $transaction->save();
            return $transaction;
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return [
                'transactionId' => $transactionId,
                'status' => $errors['code'],
                'statusDetail' => $errors['description'],
            ];
        }
    }

    /**
     * Release a transaction on SagePay
     *
     * @param string $transactionId
     * @param integer $amount
     * @return Transaction
     */
    public function releaseTransaction($transactionId, $amount = null)
    {
        $transaction = Transaction::where('transaction_id', '=', $transactionId)->first();
        try {
            $response = $this->http->post($this->host.'transactions/'.$transactionId.'/instructions', [
                'json' => [
                    'instructionType' => 'release',
                    'amount' => $amount ?: $transaction->amount,
                ],
            ]);
            $content = json_decode($response->getBody()->getContents(), true);
            $transaction->released_at = now();
            $transaction->save();
            return $transaction;
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return [
                'transactionId' => $transactionId,
                'status' => $errors['code'],
                'statusDetail' => $errors['description'],
            ];
        }
    }

    /**
     * Get the Vendor TX Code
     *
     * @param Order $order
     * @return void
     */
    protected function getVendorTxCode($order)
    {
        return base64_encode($order->encodedId().'-'.microtime(true));
    }

    /**
     * Create a base transaction
     *
     * @param array $content
     * @return Transaction
     */
    protected function getBaseTransaction($content)
    {
        $transaction = new Transaction;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->driver = 'sagepay';

        return $transaction;
    }

    /**
     * Create a refunded transaction
     *
     * @param array $content
     * @param integer $amount
     * @param string $notes
     * @return Transaction
     */
    protected function createRefundTransaction($content, $amount, $notes)
    {
        $transaction = $this->getBaseTransaction($content);
        $transaction->amount = -abs($amount);
        $transaction->card_type = $content['paymentMethod']['card']['cardType'] ?? 'Unknown';
        $transaction->last_four = $content['paymentMethod']['card']['lastFourDigits'] ?? '';
        $transaction->transaction_id = $content['transactionId'];
        $transaction->driver = 'sagepay';
        $transaction->status = $content['statusDetail'];
        $transaction->success = true;
        $transaction->notes = $notes;
        $transaction->refund = true;
        $transaction->save();

        return $transaction;
    }

    /**
     * Create a successful transaction
     *
     * @param array $content
     * @return Transaction
     */
    protected function createSuccessTransaction($content)
    {
        $transaction = new Transaction;
        $transaction->success = true;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->deferred = $content['transactionType'] == 'Deferred';
        $transaction->driver = 'sagepay';
        $transaction->status = $content['status'];
        $transaction->amount = $content['amount']['totalAmount'];
        $transaction->card_type = $content['paymentMethod']['card']['cardType'] ?? 'Unknown';
        $transaction->last_four = $content['paymentMethod']['card']['lastFourDigits'] ?? '';
        $transaction->transaction_id = $content['transactionId'];
        $transaction->address_matched = $content['avsCvcCheck']['address'] == 'Matched' ?: false;
        $transaction->cvc_matched = $content['avsCvcCheck']['securityCode'] == 'Matched' ?: false;
        $transaction->postcode_matched = $content['avsCvcCheck']['postalCode'] == 'Matched' ?: false;
        $transaction->setAttribute('threed_secure', $content['3DSecure']['status'] == 'Authenticated' ?: false);
        $transaction->save();

        return $transaction;
    }

    /**
     * Create a failed transaction.
     *
     * @param array $errors
     * @return Transaction
     */
    protected function createFailedTransaction($errors, $amount = null, $notes = null)
    {
        /**
         * Trigger an event so apps can do stuff
         */
        event(new PaymentFailedEvent($errors));

        $transaction = new Transaction;
        $transaction->success = false;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->driver = 'sagepay';
        $transaction->amount = $amount ?? $this->order->order_total;
        $transaction->notes = $notes ?: $errors['statusDetail'] ?? '-';
        $transaction->status = $errors['status'] ?? 'failed';
        $transaction->card_type = '-';
        $transaction->last_four = '-';
        $transaction->transaction_id = $errors['transactionId'] ?? 'Unknown';
        $transaction->save();

        return $transaction;
    }

    /**
     * Get the client token for authentication.
     *
     * @return void
     */
    public function getClientToken()
    {
        try {
            $response = $this->http->post($this->host.'merchant-session-keys', [
                'headers' => [
                    'Authorization' => 'Basic '.$this->getCredentials(),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
                'json' => [
                    'vendorName' => $this->getVendor(),
                ],
            ]);
        } catch (ClientException $e) {
            Log::error($e->getMessage());

            return;
        }

        $response = json_decode($response->getBody()->getContents(), true);

        $this->tokenExpires = $response['expiry'] ?? null;

        return $response['merchantSessionKey'] ?? null;
    }

    /**
     * Get the token expiry.
     *
     * @return Carbon\Carbon
     */
    public function getTokenExpiry()
    {
        return Carbon::parse($this->tokenExpires);
    }

    /**
     * Get the vendor name.
     *
     * @return string
     */
    protected function getVendor()
    {
        return config('services.sagepay.vendor');
    }

    /**
     * Get the service credentials.
     *
     * @return string
     */
    protected function getCredentials()
    {
        return base64_encode(config('services.sagepay.key').':'.config('services.sagepay.password'));
    }
}
