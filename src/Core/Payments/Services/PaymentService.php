<?php

namespace GetCandy\Api\Core\Payments\Services;

use GetCandy\Api\Core\Orders\Events\OrderRefundEvent;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Exceptions\InvalidPaymentTokenException;
use GetCandy\Api\Core\Payments\Exceptions\ThreeDSecureRequiredException;
use GetCandy\Api\Core\Payments\Exceptions\TransactionAmountException;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Payments\ThreeDSecureResponse;
use GetCandy\Api\Core\Scaffold\BaseService;

class PaymentService extends BaseService
{
    protected $configPath = 'getcandy.payments';

    protected $provider;

    /**
     * Payment Manager.
     *
     * @var \GetCandy\Api\Core\Payments\PaymentContract
     */
    protected $manager;

    public function __construct(PaymentContract $manager)
    {
        $this->model = new Transaction;
        $this->manager = $manager;
    }

    /**
     * Gets the payment provider class.
     *
     * @return mixed
     */
    public function getProvider()
    {
        if (! $this->provider) {
            $this->provider = config($this->configPath.'.gateway', 'braintree');
        }

        $provider = config(
            $this->configPath.'.providers.'.$this->provider
        );

        return app()->make($provider);
    }

    public function getProviders()
    {
        return config(
            $this->configPath.'.providers'
        );
    }

    /**
     * Set the provider.
     *
     * @param  string  $provider
     * @return mixed
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Charge the order.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @param  string|null  $token
     * @param  string|null  $type
     * @param  array  $data
     * @return mixed
     *
     * @throws \GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException
     */
    public function charge(Order $order, $token = null, $type = null, $data = [])
    {
        if ($order->placed_at) {
            throw new OrderAlreadyProcessedException;
        }

        if ($type) {
            $this->setProvider($type->driver);
        }

        return $this->getProvider()->charge($token, $order, $data);
    }

    /**
     * Process an order for payment.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @param  string  $token
     * @param  mixed  $type
     * @param  array  $fields
     * @return mixed
     *
     * @throws \GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException
     * @throws \GetCandy\Api\Core\Payments\Exceptions\InvalidPaymentTokenException
     * @throws \GetCandy\Api\Core\Payments\Exceptions\ThreeDSecureRequiredException
     */
    public function process($order, $token, $type = null, $fields = [])
    {
        if ($order->placed_at) {
            throw new OrderAlreadyProcessedException;
        }

        $manager = $this->manager->with(
            $type ? $type->driver : null
        );

        if (! $manager->validate($token)) {
            throw new InvalidPaymentTokenException;
        }

        $response = $manager
            ->token($token)
            ->order($order)
            ->fields($fields)
            ->charge();

        if ($response instanceof ThreeDSecureResponse) {
            throw new ThreeDSecureRequiredException($response);
        }

        return $response;
    }

    /**
     * Refund a sale.
     *
     * @param  string  $id
     * @param  int  $amount
     * @param  string|null $notes
     * @return mixed
     */
    public function refund($id, int $amount, $notes = null)
    {
        $transaction = $this->getByHashedId($id);

        // Get all transactions that are refunds.
        $refunds = $transaction->order->transactions()->whereRefund(true)->sum('amount');

        if ($amount > ($transaction->amount - $refunds)) {
            throw new TransactionAmountException;
        }

        $manager = $this->manager->with(
            $transaction->driver
        );

        $refund = $manager->order($transaction->order)->refund(
            $transaction->transaction_id,
            $amount ?: $transaction->amount,
            $notes ?: 'Refund'
        );

        if ($notes) {
            $refund->update([
                'notes' => $notes,
            ]);
        }

        event(new OrderRefundEvent($transaction->order, $refund));

        return $refund;
    }

    /**
     * Validate a 3DSecure transaction.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @param  string  $transactionId - The transaction ID from the provider
     * @param  string  $paRes - The encoded response from the 3DSecure form
     * @param  mixed  $type
     * @return \GetCandy\Api\Core\Payments\Models\Transaction
     */
    public function validateThreeD($order, $transactionId, $paRes, $type = null)
    {
        $manager = $this->manager->with(
            $type ? $type->driver : null
        );

        $transaction = $manager->order($order)
            ->processThreeD($transactionId, $paRes);

        return $transaction;
    }

    /**
     * Creates a transaction.
     *
     * @param  array  $data
     * @return \GetCandy\Api\Core\Payments\Models\Transaction
     */
    protected function createTransaction(array $data)
    {
        $transaction = new Transaction;
        $transaction->success = $data['success'];
        $transaction->status = $data['status'];
        $transaction->amount = $data['amount'];
        $transaction->transaction_id = $data['transaction_id'];
        $transaction->merchant = $data['merchant'];
        $transaction->order_id = $data['order']->id;
        $transaction->notes = $data['notes'];
        $transaction->save();

        return $transaction;
    }

    /**
     * Voids a transaction.
     *
     * @param  string  $transactionId
     * @return \GetCandy\Api\Core\Payments\Models\Transaction
     */
    public function void($transactionId)
    {
        $transaction = $this->getByHashedId($transactionId);
        $order = $transaction->order;

        $result = $this->getProvider()->void($transaction->transaction_id);

        $transaction->success = $result->success;
        $transaction->status = 'voided';

        if (! $result->success) {
            $transaction->notes = $result->message;
        }

        // TODO Improve the way this is checked...
        if ($order->transactions()->charged()->count() === 1 && $order->total === $transaction->amount) {
            $order->status = 'voided';
            $order->save();
        }

        $transaction->save();

        return $transaction;
    }
}
