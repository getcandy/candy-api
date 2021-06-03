<?php

namespace GetCandy\Api\Core\Payments\Providers;

use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\Capture;
use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;
use PayPal\Api\RefundRequest;
use PayPal\Auth\OAuthTokenCredential;
use GetCandy\Api\Core\Payments\PaymentResponse;
use PayPal\Exception\PayPalConnectionException;
use GetCandy\Api\Core\Payments\Models\Transaction;

class PayPal extends AbstractProvider
{
    /**
     * The Guzzle client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The environment context.
     *
     * @var \PayPal\Rest\ApiContext
     */
    protected $context;

    /**
     * PayPal payment details.
     *
     * @var \PayPal\Api\Payment
     */
    protected $details;

    public function __construct()
    {
        $config = config('services.paypal');
        $settings = $config['settings'] ?? [
            'mode' => 'sandbox',
        ];
        $credentials = $config[$settings['mode'] ?? 'sandbox'];

        $this->context = new ApiContext(
            new OAuthTokenCredential(
                $credentials['client_id'],
                $credentials['client_secret']
            )
        );
        $this->context->setConfig($settings);
    }

    public function getName()
    {
        return 'PayPal';
    }

    /**
     * Checks whether the token is valid.
     *
     * @param  string  $token
     * @return bool
     */
    public function validate($token)
    {
        $sale = new Payment;
        try {
            $this->details = $sale->get($token, $this->context);
        } catch (PayPalConnectionException $e) {
            return false;
        }

        return true;
    }

    public function charge()
    {
        $transactions = $this->getTransactions();

        // Get our successful transaction
        $success = $transactions->first(function ($t) {
            return $t->success;
        });

        if (! $success) {
            $response = new PaymentResponse(false, 'Unable to process order');
            $response->transaction = $transactions->first(function ($t) {
                return ! $t->success;
            });

            return $response;
        }

        $response = new PaymentResponse(true, 'Payment Received');
        $response->transaction($success);

        return $response;
    }

    /**
     * Create a successful transaction.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getTransactions()
    {
        $transactions = collect();

        foreach ($this->details->getTransactions() as $transaction) {
            $candyTrans = new Transaction;

            $resources = $transaction->getRelatedResources();

            foreach ($resources as $resource) {
                $candyTrans->success = $resource->getSale()->getState() == 'completed';
                $candyTrans->status = $resource->getSale()->getState();
                $candyTrans->transaction_id = $resource->getSale()->getId();
            }

            $candyTrans->order()->associate($this->order);
            $candyTrans->merchant = $transaction->getPayee()->getMerchantId();
            $candyTrans->provider = 'PayPal';
            $candyTrans->driver = 'paypal';
            $candyTrans->card_type = 'Express Checkout';
            $candyTrans->last_four = '';

            $candyTrans->amount = $transaction->getAmount()->getTotal() * 100;

            $transactions->push($candyTrans);

            $candyTrans->save();
        }

        return $transactions;
    }

    public function refund($token, $amount, $description)
    {
        try {
            $paypalAmount = new Amount;
            $paypalAmount->setCurrency("GBP")
                ->setTotal($amount / 100);

            $refundRequest = new RefundRequest;
            $refundRequest->setAmount($paypalAmount);

            // ### Retrieve Capture paypalAmountdetails
            $capture = Capture::get($token, $this->context);

            // ### Refund the Capture
            $captureRefund = $capture->refundCapturedPayment($refundRequest, $this->context);

            $transaction = new Transaction;
            $transaction->success = true;
            $transaction->refund = true;
            $transaction->order()->associate($this->order);
            $transaction->merchant = 'N/A';
            $transaction->provider = 'PayPal';
            $transaction->driver = 'paypal';
            $transaction->amount = -abs($captureRefund->amount->total * 100);
            $transaction->notes = null;
            $transaction->status = $captureRefund->state;
            $transaction->card_type = '-';
            $transaction->last_four = '-';
            $transaction->transaction_id = $captureRefund->capture_id;
            $transaction->save();

            return $transaction;
            
        } catch (PayPalConnectionException $e) {
            $errors = json_decode($e->getData());
            $response = new PaymentResponse(false, 'Refund Failed', json_decode($e->getData(), true));

            $transaction = new Transaction;
            $transaction->success = false;
            $transaction->refund = true;
            $transaction->order()->associate($this->order);
            $transaction->merchant = 'N/A';
            $transaction->provider = 'PayPal';
            $transaction->driver = 'paypal';
            $transaction->amount = $amount;
            $transaction->notes = $errors->message;
            $transaction->status = $errors->name;
            $transaction->card_type = '-';
            $transaction->last_four = '-';
            $transaction->transaction_id = $token;
            $transaction->save();

            return $transaction;
        }
    }

    public function getClientToken()
    {
    }
}
