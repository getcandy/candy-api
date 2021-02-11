<?php

namespace GetCandy\Api\Core\Payments\Providers;

use Stripe\StripeClient;
use Stripe\PaymentIntent;
use GetCandy\Api\Core\Payments\PaymentResponse;
use GetCandy\Api\Core\Payments\Models\Transaction;

class StripeIntents extends AbstractProvider
{
    /**
     * @var string
     */
    protected $name = 'Stripe Intents';

    public function getName()
    {
        return $this->name;
    }

    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function validate($token)
    {
        return true;
    }

    public function getClientToken()
    {
        $client = $this->getClient();

        // Do we already have a payment intent for this order?
        $meta = $this->order->meta;

        $paymentIntentToken = $meta['stripe_payment_intent'] ?? null;

        if (!$paymentIntentToken) {
            $intent = $client->paymentIntents->create([
                'amount' => $this->order->order_total,
                'currency' => $this->order->currency,
                // Verify your integration in this guide by including this parameter
                'metadata' => ['integration_check' => 'accept_a_payment'],
            ]);
            $meta['stripe_payment_intent'] = $intent->id;
            $this->order->update([
                'meta' => $meta
            ]);
            return $intent->client_secret;
        } else {
            $intent = $client->paymentIntents->retrieve(
                $paymentIntentToken,
                []
            );
            if ($intent->amount != $this->order->order_total) {
                $client->paymentIntents->update(
                    $intent->id,
                    [
                        'amount' => $this->order->order_total
                    ]
                );
            }
        }

        return $intent->client_secret;
    }

    public function updateTransaction($transaction)
    {
        return true;
    }

    public function getClient()
    {
        return new StripeClient(config('services.stripe.key'));
    }

    public function charge()
    {
        $order = $this->order;
        $meta = $this->order->meta;
        $paymentIntentToken = $meta['stripe_payment_intent'] ?? null;

        if (!$paymentIntentToken) {
            return new PaymentResponse(false);
        }

        $paymentIntent = $this->getPaymentIntent($paymentIntentToken);

        if ($paymentIntent->status != 'succeeded') {
            return new PaymentResponse(true);
        }

        $paymentMethod = $this->getClient()->paymentMethods->retrieve($paymentIntent->payment_method);
        $threedchecks = $paymentMethod->card->checks;

        $transaction = new Transaction;
        $transaction->amount = $paymentIntent->amount;
        $transaction->order_id = $this->order->id;
        $transaction->transaction_id = $paymentIntent->id;
        $transaction->success = true;
        $transaction->last_four = $paymentMethod->card->last4;
        $transaction->merchant = 'Stripe';
        $transaction->driver = 'stripe-intents';
        $transaction->provider = 'Stripe';
        $transaction->card_type = $paymentMethod->card->brand;
        $transaction->address_matched = $threedchecks->address_line1_check === 'pass';
        $transaction->cvc_matched = $threedchecks->cvc_check === 'pass';
        $transaction->threed_secure = $paymentMethod->card->three_d_secure_usage->supported ?? false;
        $transaction->postcode_matched = $threedchecks->address_postal_code_check === 'pass';
        $transaction->status = $paymentIntent->status;
        $transaction->save();
        // Get the payment method

        $response = new PaymentResponse(true, 'Payment Success', []);
        return $response->transaction($transaction);
    }

    protected function getPaymentIntent($intentId)
    {
        return $this->getClient()->paymentIntents->retrieve(
            $intentId,
            []
        );
    }

    public function refund($token, $amount, $description)
    {
        return true;
    }
}
