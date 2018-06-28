<?php
namespace GetCandy\Api\Core\Payments\Providers;

use Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Models\Transaction;

class SagePay extends AbstractProvider
{
    protected $host = 'https://pi-test.sagepay.com/api/v1/';

    protected $tokenExpires;

    public function getName()
    {
        return 'SagePay';
    }

    public function validateToken($token)
    {
        return true;
    }

    public function charge($token, Order $order, $data = [])
    {
        // Get the billing country
        $country = app('api')->countries()->getByName($order->billing_country);

        if (!$country) {
            $countryIso = 'GB';
        } else {
            $countryIso = $country->iso_a_2;
        }

        $client = new Client([
            'base_uri' => $this->host
        ]);

        try {
            $payload = [
                'transactionType' => 'Payment',
                'paymentMethod' => [
                    'card' => [
                        'merchantSessionKey' => $data['merchant_key'] ?? $this->getClientToken(),
                        'cardIdentifier' => $token
                    ]
                ],
                'amount' => $order->order_total,
                'currency' => $order->currency,
                'description' => 'Website Transaction',
                'apply3DSecure' => 'UseMSPSetting',
                'customerFirstName' => $order->billing_firstname,
                'customerLastName' => $order->billing_lastname,
                'vendorTxCode' => str_random(40),
                'billingAddress' => [
                    'address1' => $order->billing_address,
                    'city' => $order->billing_city,
                    'postalCode' => $order->billing_zip,
                    'country' => $countryIso
                ],
                'entryMethod' => 'Ecommerce'
            ];

            $response = $client->request('POST', 'transactions', [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->getCredentials(),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
                'json' => $payload
            ]);


        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $this->createFailedTransaction($errors, $order);
        }

        $content = json_decode($response->getBody()->getContents(), true);
        return $this->createSuccessTransaction($content, $order);
    }

    protected function getVendorTxCode($order)
    {
        return base64_encode($order->encodedId() . '-' . microtime(true));
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

    protected function createFailedTransaction($errors, $order)
    {
        $transaction = new Transaction;
        $transaction->success = false;
        $transaction->order()->associate($order);
        $transaction->merchant = $this->getVendor();
        $transaction->provider = 'SagePay';
        $transaction->notes = $errors['description'] ?? null;
        $transaction->amount = $order->order_total;
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
            'base_uri' => $this->host
        ]);

        try {
            $response = $client->request('POST', 'merchant-session-keys', [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->getCredentials(),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
                'json' => [
                    'vendorName' => $this->getVendor()
                ]
            ]);
        } catch (ClientException $e) {
            Log::error($e->getMessage());
            return null;
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
        return base64_encode(config('services.sagepay.key') . ':' . config('services.sagepay.password'));
    }
}