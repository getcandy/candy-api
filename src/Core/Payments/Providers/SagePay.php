<?php

namespace GetCandy\Api\Core\Payments\Providers;

use Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GetCandy\Api\Core\Payments\PaymentResponse;
use GetCandy\Api\Core\Payments\Models\Transaction;

class SagePay extends AbstractProvider
{
    protected $host = 'https://pi-test.sagepay.com/api/v1/';

    protected $tokenExpires;

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
                "description" => $description,
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
            return $this->createFailedTransaction($errors, $amount);
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

        $response = new PaymentResponse(true, 'Payment Received');
        $response->transaction(
            $this->createSuccessTransaction($content, $this->order)
        );

        return $response;
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

    protected function createSuccessTransaction($content, $order)
    {
        $transaction = new Transaction;

        $transaction->success = true;
        $transaction->order()->associate($order);
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
        $transaction->setAttribute('threed_secure', $content['3DSecure']['status'] == 'Checked' ?: false);
        $transaction->save();

        return $transaction;
    }

    /**
     * Create a failed transaction
     *
     * @param array $errors
     * @return Transaction
     */
    protected function createFailedTransaction($errors, $amount = null)
    {
        $transaction = new Transaction;
        $transaction->success = false;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->amount = $amount ?? $this->order->order_total;
        $transaction->status = $errors['description'] ?? null;
        $transaction->card_type = '-';
        $transaction->last_four = '-';
        $transaction->transaction_id = 'Unknown';
        $transaction->save();

        return $transaction;
    }

    /**
     * Get the client token for authentication
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
     * Get the token expiry
     *
     * @return Carbon\Carbon
     */
    public function getTokenExpiry()
    {
        return Carbon::parse($this->tokenExpires);
    }

    /**
     * Get the vendor name
     *
     * @return string
     */
    protected function getVendor()
    {
        return config('services.sagepay.vendor');
    }

    /**
     * Get the service credentials
     *
     * @return string
     */
    protected function getCredentials()
    {
        return base64_encode(config('services.sagepay.key').':'.config('services.sagepay.password'));
    }
}
