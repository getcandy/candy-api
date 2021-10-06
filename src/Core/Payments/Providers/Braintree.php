<?php

namespace GetCandy\Api\Core\Payments\Providers;

use Braintree_Exception_NotFound;
use Braintree_Gateway;
use Braintree_Transaction;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Core\Payments\PaymentResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Braintree extends AbstractProvider
{
    protected $name = 'Braintree';

    /**
     * The Braintree api gateway.
     *
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
        $user = Auth::user();

        if ($user) {
            // Does the user have a provider id?
            $provider = $user->providerUsers()->provider('braintree')->first();

            if ($provider) {
                try {
                    return $this->gateway->clientToken()->generate([
                        'customerId' => $provider->provider_id,
                    ]);
                } catch (\Exception $e) {
                    // Fall through to guest
                }
            }
        }

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

        $user = $this->order->user;
        $customerId = null;
        $paymentToken = $this->token;

        $payload = [
            'amount' => $this->order->order_total / 100,
            'paymentMethodNonce' => $paymentToken,
            'merchantAccountId' => $merchant,
            'customerId' => $customerId,
            'customer' => [
                'firstName' => $billing['firstname'],
                'lastName' => $billing['lastname'],
                'email' => $user ? $user->email : null,
            ],
            'billing' => [
                'firstName' => $billing['firstname'],
                'lastName' => $billing['lastname'],
                'locality' => $billing['city'],
                'region' => $billing['county'] ?: $billing['state'],
                'postalCode' => $billing['zip'],
                'streetAddress' => $billing['address'],
            ],
            'shipping' => [
                'firstName' => $shipping['firstname'],
                'lastName' => $shipping['lastname'],
                'locality' => $shipping['city'],
                'region' => $shipping['county'] ?: $shipping['state'],
                'postalCode' => $shipping['zip'],
                'streetAddress' => $shipping['address'],
            ],
            'options' => [
                'submitForSettlement' => true,
            ],
        ];
        // If we have a user, then create a customer in the Vault...
        if ($user) {
            $providerUser = $user->providerUsers()->provider('braintree')->first();

            if (! $providerUser) {
                $result = $this->gateway->customer()->create([
                    'firstName' => $billing['firstname'],
                    'lastName' => $billing['lastname'],
                    'email' => $user->email,
                    // 'billingAddress' => [
                    //     'firstName' => $billing['firstname'],
                    //     'lastName' => $billing['lastname'],
                    //     'locality' => $billing['city'],
                    //     'region' =>   $billing['county'] ?: $billing['state'],
                    //     'postalCode' =>   $billing['zip'],
                    //     'streetAddress' => $billing['address'],
                    // ]
                ]);

                if ($result->success) {
                    $user->providerUsers()->create([
                        'provider' => 'braintree',
                        'provider_id' => $result->customer->id,
                    ]);
                }
                $customerId = $result->customer->id;
            } else {
                $customerId = $providerUser->provider_id;
            }

            // Do we want to save this card?
            if ($customerId && ! empty($this->fields['save'])) {
                $result = $this->gateway->paymentMethod()->create([
                    'customerId' => $customerId,
                    'paymentMethodNonce' => $this->token,
                ]);

                $paymentMethod = $result->paymentMethod;

                $user->reusablePayments()->create([
                    'type' => $paymentMethod->cardType,
                    'provider' => 'braintree',
                    'last_four' => $paymentMethod->last4,
                    'token' => $paymentMethod->token,
                    'expires_at' => Carbon::createFromFormat('d-m-Y', "01-{$paymentMethod->expirationMonth}-{$paymentMethod->expirationYear}"),
                ]);
                unset($payload['paymentMethodNonce']);
                $payload['paymentMethodToken'] = $paymentMethod->token;
            }
        }

        $sale = $this->gateway->transaction()->sale($payload);

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
     * @param  array  $result
     *
     * @return \GetCandy\Api\Core\Payments\Models\Transaction
     */
    protected function createFailedTransaction($result)
    {
        $transaction = new Transaction();
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
        $transaction = new Transaction();
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
        $result = $this->gateway->transaction()->refund($token, $amount / 100);

        $transactionModel = Transaction::where('transaction_id', '=', $token)->first();

        $fullRefund = $amount == $transactionModel->amount;

        if (! $result->success && $fullRefund) {
            $error = collect($result->errors->forKey('transaction')->shallowAll())->first();
            // Trying to refund a transaction that isn't settled.
            if ($error->code == '91506') {
                $result = $this->gateway->transaction()->void($token);
            }
        }

        $responseT = $result->transaction;

        $transaction = new Transaction();
        $transaction->success = $result->success;
        $transaction->order()->associate($this->order);
        $transaction->merchant = $responseT ? $responseT->merchantAccountId : 'Unknown';
        $transaction->provider = 'Braintree';
        $transaction->driver = 'braintree';
        $transaction->refund = true;
        $transaction->status = $responseT ? $result->transaction->status : $result->message;
        $transaction->amount = -abs($amount);
        $transaction->card_type = $responseT ? ($result->transaction->creditCardDetails->cardType ?? 'Unknown') : 'Unknown';
        $transaction->last_four = $responseT ? ($result->transaction->creditCardDetails->last4 ?? '') : '';
        $transaction->transaction_id = $responseT ? $result->transaction->id : Str::random();
        $transaction->address_matched = false;
        $transaction->cvc_matched = false;
        $transaction->postcode_matched = false;
        $transaction->save();

        return $transaction;
    }

    public function void($token)
    {
        $result = Braintree_Transaction::void($token);

        return $result;
    }

    public function deleteReusablePayment($payment)
    {
        try {
            $this->gateway->paymentMethod()->delete($payment->token);
        } catch (\Braintree\Exception\NotFound $e) {
        }
    }
}
