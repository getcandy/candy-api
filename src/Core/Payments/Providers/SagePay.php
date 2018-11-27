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

class SagePay extends AbstractProvider
{
    protected $host;

    protected $tokenExpires;

    public function __construct()
    {
        $this->host = config('services.sagepay.host', 'https://pi-test.sagepay.com/api/v1/');
    }

    public function getName()
    {
        return 'SagePay';
    }

    public function validate($token)
    {
        // SagePay doesn't seem to have a good way to
        // check this, so we just return true.
        return true;
    }

    public function refund($token, $amount, $description)
    {
        $client = new Client([
            'base_uri' => $this->host,
        ]);

        try {
            $payload = [
                'transactionType' => 'Refund',
                'amount' => $amount,
                'referenceTransactionId' => $token,
                'currency' => $this->order->currency, // Get currency from order.
                'vendorTxCode' => str_random(40),
                'description' => $description,
            ];

            $response = $client->request('POST', 'transactions', [
                'headers' => [
                    'Authorization' => 'Basic '.$this->getCredentials(),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
                'json' => $payload,
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            $response = new PaymentResponse(false, 'Refund Failed', $errors);

            return $this->createFailedTransaction($errors, $amount, $description);
        }

        $content = json_decode($response->getBody()->getContents(), true);

        return $this->createRefundTransaction($content, $amount, $description);
    }

    public function charge()
    {
        $client = new Client([
            'base_uri' => $this->host,
        ]);

        $country = $this->order->billing_country;

        // Sage pay requires the country iso code, so we should find that to use.
        // $countryModel = app('api')->countries()->getByName($country);

        // if ($countryModel) {
        //     $country = $countryModel->iso_a_2;
        // }
        // // This breaks maria DB

        try {
            $payload = [
                'transactionType' => 'Payment',
                'paymentMethod' => [
                    'card' => [
                        'merchantSessionKey' => $this->fields['merchant_key'] ?? $this->getClientToken(),
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

            \Log::info(json_encode($payload));

            $response = $client->request('POST', 'transactions', [
                'headers' => [
                    'Authorization' => 'Basic '.$this->getCredentials(),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
                'json' => $payload,
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            $response = new PaymentResponse(false, 'Payment Failed', $errors);
            $response->transaction(
                $this->createFailedTransaction($errors)
            );

            return $response;
        }

        $content = json_decode($response->getBody()->getContents(), true);

        // If it's 3DSecured then we return the relevant response
        if ($content['status'] == '3DAuth') {
            return (new ThreeDSecureResponse)
                ->setStatus($content['statusCode'])
                ->setTransactionId($content['transactionId'])
                ->setPaRequest($content['paReq'])
                ->setRedirect($content['acsUrl']);
        }

        $response = new PaymentResponse(true, 'Payment Received');

        if (! empty($content['paymentMethod']['card']['reusable'])) {
            $this->saveCard($content['paymentMethod']['card']);
        }

        $response->transaction(
            $this->createSuccessTransaction($content, $this->order)
        );

        return $response;
    }

    protected function saveCard($details)
    {
        $identifier = $details['cardIdentifier'];
        $userId = $this->order->user_id;
        $exists = ReusablePayment::where('token', '=', $identifier)
                    ->where('user_id', '=', $userId)->exists();

        // If this card already exists for this user. Don't add it again.
        if ($exists) {
            return;
        }

        $payment = new ReusablePayment;
        $payment->type = strtolower($details['cardType']);
        $payment->provider = 'sagepay';
        $payment->last_four = $details['lastFourDigits'];
        $payment->expires_at = \Carbon\Carbon::createFromFormat('my', $details['expiryDate'])->endOfMonth();
        $payment->token = $details['cardIdentifier'];
        $payment->user_id = $this->order->user_id;
        $payment->save();
    }

    public function processThreeD($transaction, $paRes)
    {
        // https://pi-test.sagepay.com/api/v1/transactions/<transactionId>/3d-secure

        $client = new Client([
            'base_uri' => $this->host,
        ]);

        try {
            $response = $client->request('POST', 'transactions/'.$transaction.'/3d-secure', [
                'headers' => [
                    'Authorization' => 'Basic '.$this->getCredentials(),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
                'json' => ['paRes' => $paRes],
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);

            return $this->createFailedTransaction([
                'statusDetail' => $errors['description'],
                'status' => 'failed',
                'transactionId' => $transaction,
            ]);
        }

        $content = json_decode($response->getBody()->getContents(), true);

        if ($content['status'] != 'Authenticated') {
            return $this->createFailedTransaction([
                'statusDetail' => '3D Secure Failed',
                'status' => 'failed',
                'transactionId' => $transaction,
            ]);
        }

        // We are authenticated, so lets get the transaction from the API
        $transaction = $this->getTransactionFromApi($transaction);

        if ($transaction['status'] != 'Ok') {
            return $this->createFailedTransaction($transaction);
        }

        if (! empty($transaction['paymentMethod']['card']['reusable'])) {
            $this->saveCard($transaction['paymentMethod']['card']);
        }

        return $this->createSuccessTransaction($transaction);
    }

    protected function getTransactionFromApi($id)
    {
        $client = new Client([
            'base_uri' => $this->host,
        ]);

        try {
            $response = $client->request('GET', 'transactions/'.$id, [
                'headers' => [
                    'Authorization' => 'Basic '.$this->getCredentials(),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
            ]);
        } catch (ClientException $e) {
            return;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function getVendorTxCode($order)
    {
        return base64_encode($order->encodedId().'-'.microtime(true));
    }

    protected function getBaseTransaction($content)
    {
        $transaction = new Transaction;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->driver = 'sagepay';

        return $transaction;
    }

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

    protected function createSuccessTransaction($content)
    {
        $transaction = new Transaction;

        $transaction->success = true;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
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
        $client = new Client([
            'base_uri' => $this->host,
        ]);

        try {
            $response = $client->request('POST', 'merchant-session-keys', [
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
