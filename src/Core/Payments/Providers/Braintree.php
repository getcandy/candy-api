<?php

namespace GetCandy\Api\Core\Payments\Providers;

use Braintree_Gateway;
use Braintree_Transaction;
use Braintree_Exception_NotFound;
use GetCandy\Api\Core\Payments\PaymentResponse;
use GetCandy\Api\Core\Payments\Models\Transaction;

class Braintree extends AbstractProvider
{
    protected $name = 'Braintree';

    /**
     * The Braintree api gateway.
     * @var Braintree_Gateway
     */
    protected $gateway;

    public function __construct()
    {
        $this->gateway = new Braintree_Gateway([
            'environment' => config('getcandy.payments.environment'),
            'merchantId' => config('services.braintree.merchant_id'),
            'publicKey' => config('services.braintree.key'),
            'privateKey' => config('services.braintree.secret'),
        ]);
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
        return $this->gateway->clientToken()->generate();
    }

    public function threeDSecured()
    {
        return config('services.braintree.3D_secure');
    }

    public function validate($token)
    {
        try {
            $token = $this->gateway->paymentMethodNonce()->find($token);
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

    public function charge()
    {
        $merchant = $this->getMerchant($this->order->currency);

        $billing = $this->order->billingDetails;
        $shipping = $this->order->shippingDetails;

        $sale = $this->gateway->transaction()->sale([
            'amount' => $this->order->order_total / 100,
            'paymentMethodNonce' => $this->token,
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

        if ($sale->success) {
            $response = new PaymentResponse(true, 'Payment Pending');

            return $response->transaction(
                $this->createSuccessTransaction($sale)
            );
        }

        $response = new PaymentResponse(false, 'Payment Failed');

        return $response->transaction(
            $this->createFailedTransaction($sale)
        );
    }

    /**
     * Create a failed transaction.
     *
     * @param array $errors
     * @return Transaction
     */
    protected function createFailedTransaction($result)
    {
        $transaction = new Transaction;
        $transaction->success = false;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $result->transaction->merchantAccountId;
        $transaction->provider = 'Braintree';
        $transaction->driver = 'braintree';
        $transaction->amount = $result->transaction->amount * 100;
        $transaction->notes = $result->message;
        $transaction->status = $result->transaction->status;
        $transaction->transaction_id = $result->transaction->id;
        $transaction->card_type = $result->transaction->creditCardDetails->cardType ?? 'Unknown';
        $transaction->last_four = $result->transaction->creditCardDetails->last4 ?? '';
        $transaction->address_matched = $result->transaction->avsStreetAddressResponseCode == 'M' ?: false;
        $transaction->cvc_matched = $result->transaction->cvvResponseCode == 'M' ?: false;
        $transaction->postcode_matched = $result->transaction->avsPostalCodeResponseCode == 'M' ?: false;
        $transaction->save();

        return $transaction;
    }

    protected function createSuccessTransaction($result)
    {
        $transaction = new Transaction;
        $transaction->success = true;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $result->transaction->merchantAccountId;
        $transaction->provider = 'Braintree';
        $transaction->driver = 'braintree';
        $transaction->status = $result->transaction->status;
        $transaction->amount = $result->transaction->amount * 100;
        $transaction->card_type = $result->transaction->creditCardDetails->cardType ?? 'Unknown';
        $transaction->last_four = $result->transaction->creditCardDetails->last4 ?? '';
        $transaction->transaction_id = $result->transaction->id;
        $transaction->address_matched = $result->transaction->avsStreetAddressResponseCode == 'M' ?: false;
        $transaction->cvc_matched = $result->transaction->cvvResponseCode == 'M' ?: false;
        $transaction->postcode_matched = $result->transaction->avsPostalCodeResponseCode == 'M' ?: false;
        $transaction->setAttribute('threed_secure', $result->transaction->threeDSecureInfo == 'Authenticated' ?: false);
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

    public function refund($token, $amount, $description)
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
