<?php

namespace GetCandy\Api\Core\Payments\Providers;

use Braintree_ClientToken;
use Braintree_Transaction;
use Braintree_Configuration;
use Braintree_Test_Transaction;
use Braintree_Exception_NotFound;
use Braintree_PaymentMethodNonce;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Models\Transaction;

class Braintree extends AbstractProvider
{
    protected $name = 'Braintree';

    public function __construct()
    {
        Braintree_Configuration::environment(config('getcandy.payments.environment'));
        Braintree_Configuration::merchantId(config('services.braintree.merchant_id'));
        Braintree_Configuration::publicKey(config('services.braintree.key'));
        Braintree_Configuration::privateKey(config('services.braintree.secret'));
    }

    public function getName()
    {
        return $this->name;
    }

    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getClientToken()
    {
        return Braintree_ClientToken::generate();
    }

    public function threeDSecured()
    {
        return config('services.braintree.3D_secure');
    }

    //TODO: REMOVE BEFORE LIVE
    private function settle($sale)
    {
        if (! app()->isLocal()) {
            return $sale;
        }

        return Braintree_Test_Transaction::settle($sale->transaction->id);
    }

    public function validateToken($token)
    {
        try {
            $token = Braintree_PaymentMethodNonce::find($token);

            if ($token->description == 'PayPal') {
                $this->setName($token->description);
            }

            if (! property_exists($token, 'threeDSecureInfo')) {
                $info = [];
            } else {
                $info = $token->threeDSecureInfo;
            }

            if ($token->consumed || (empty($info) && $this->threeDSecured())) {
                return false;
            }
        } catch (Braintree_Exception_NotFound $e) {
            return false;
        }

        return true;
    }

    public function getMerchant($currency = null)
    {
        return config(
            'services.braintree.merchants.'.strtolower($currency),
            config('services.braintree.merchants.default')
        );
    }

    public function charge($token, Order $order)
    {
        $merchant = $this->getMerchant($order->currency);

        $billing = $order->billingDetails;
        $shipping = $order->shippingDetails;

        $sale = Braintree_Transaction::sale([
            'amount' => $order->total,
            'paymentMethodNonce' => $token,
            'merchantAccountId' => $merchant,
            'customer' => [
                'firstName' => $billing['firstname'],
                'lastName' => $billing['lastname'],
            ],
            'billing' => [
                'firstName' => $billing['firstname'],
                'lastName' => $billing['lastname'],
                'locality' => $billing['city'],
                'region' =>   $billing['county'] ?: $billing['state'],
                'postalCode' =>   $billing['zip'],
                'streetAddress' => $billing['address'],
            ],
            'shipping' => [
                'firstName' => $shipping['firstname'],
                'lastName' => $shipping['lastname'],
                'locality' => $shipping['city'],
                'region' => $shipping['county'] ?: $shipping['state'],
                'postalCode' =>   $shipping['zip'],
                'streetAddress' => $shipping['address'],
            ],
            'options' => [
                'submitForSettlement' => true,
            ],
        ]);

        $transaction = $this->createTransaction($sale, $order);

        return $transaction->success;
    }

    protected function createTransaction($result, $order)
    {
        $transaction = new Transaction;

        $transaction->success = $result->success;
        $transaction->order()->associate($order);
        $transaction->merchant = $result->transaction->merchantAccountId;

        $transaction->provider = $result->transaction->paymentInstrumentType;
        $transaction->status = $result->transaction->status;
        $transaction->amount = $result->transaction->amount;
        $transaction->card_type = $result->transaction->creditCardDetails->cardType ?? '';
        $transaction->last_four = $result->transaction->creditCardDetails->last4 ?? '';

        if ($result->transaction) {
            $transaction->transaction_id = $result->transaction->id;
        } else {
            $transaction->transaction_id = 'Unknown';
        }

        $transaction->save();

        return $transaction;
    }

    public function updateTransaction($transaction)
    {
        if (! $transaction->transaction_id) {
            return;
        }
        $remote = Braintree_Transaction::find($transaction->transaction_id);
        $transaction->update([
            'status' => $remote->status,
        ]);

        if ($transaction->order->status == 'payment-processing') {
            if ($transaction->status == 'settled') {
                $order = $transaction->order;
                $order->status = 'payment-received';
                $order->save();
            } elseif ($transaction->status == 'gateway_rejected' || $transaction->status == 'processor_declined') {
                $order = $transaction->order;
                $order->status = 'failed';
                $order->save();
            }
        }
    }

    public function refund($token, $amount = null)
    {
        $transaction = Braintree_Transaction::refund($token, $amount);

        return $transaction;
    }

    public function void($token)
    {
        $result = Braintree_Transaction::void($token);

        return $result;
    }
}
