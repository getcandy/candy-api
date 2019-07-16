<?php

namespace GetCandy\Api\Core\Payments\Providers;

use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\PaymentResponse;
use PayPal\Exception\PayPalConnectionException;
use GetCandy\Api\Core\Payments\Models\Transaction;

class PayPal extends AbstractProvider
{
    /**
     * The Guzzle client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The environment context.
     *
     * @var ApiContext
     */
    protected $context;

    /**
     * PayPal payment details.
     *
     * @var Payment
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
     * @param string $token
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
     * @param [type] $content
     * @param [type] $order
     * @return void
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
    }

    public function getClientToken()
    {
    }
}
