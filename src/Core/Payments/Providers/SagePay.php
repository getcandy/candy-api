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

    public function charge()
    {
        $client = new Client([
            'base_uri' => $this->host,
        ]);

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
                    'country' => $this->order->billing_country,
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

    protected function createSuccessTransaction($content, $order)
    {
        $transaction = new Transaction;

        $transaction->success = true;
        $transaction->order()->associate($order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->status = $content['status'];
        $transaction->amount = $content['amount']['totalAmount'];
        $transaction->card_type = $content['paymentMethod']['card']['cardType'] ?? 'Unknown';
        $transaction->last_four = $content['paymentMethod']['card']['lastFourDigits'] ?? '';
        $transaction->transaction_id = $content['transactionId'];

        $transaction->save();

        return $transaction;
    }

    protected function createFailedTransaction($errors)
    {
        $transaction = new Transaction;
        $transaction->success = false;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->notes = $errors['description'] ?? null;
        $transaction->amount = $this->order->order_total;
        $transaction->status = 'failed';
        $transaction->card_type = '-';
        $transaction->last_four = '-';
        $transaction->transaction_id = 'Unknown';
        $transaction->save();

        return $transaction;
    }

    public function refund($token, $amount = null)
    {
    }

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

    public function getTokenExpiry()
    {
        return Carbon::parse($this->tokenExpires);
    }

    protected function getVendor()
    {
        return config('services.sagepay.vendor');
    }

    protected function getCredentials()
    {
        return base64_encode(config('services.sagepay.key').':'.config('services.sagepay.password'));
    }
}
