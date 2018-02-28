<?php
namespace GetCandy\Api\Payments\Services;

use GetCandy\Api\Orders\Models\Order;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Payments\Models\Transaction;
use GetCandy\Api\Payments\Exceptions\AlreadyRefundedException;

class PaymentService extends BaseService
{
    protected $configPath = 'getcandy.payments';

    protected $provider;

    public function __construct()
    {
        $this->model = new Transaction;
    }

    /**
     * Gets the payment provider class
     *
     * @return void
     */
    public function getProvider()
    {
        if (!$this->provider) {
            $this->provider = config($this->configPath . '.gateway', 'braintree');
        }

        $provider = config(
            $this->configPath . '.providers.' . $this->provider
        );

        return app()->make($provider);
    }

    /**
     * Set the provider
     *
     * @param string $provider
     * @return mixed
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Validates a payment token
     *
     * @param string $token
     *
     * @return void
     */
    public function validateToken($token)
    {
        return $this->getProvider()->validateToken($token);
    }

    /**
     * Charge the order
     *
     * @param Order $order
     * @param string $token
     * @param string $type
     *
     * @return boolean
     */
    public function charge(Order $order, $token = null, $type = null)
    {
        if ($type) {
            $this->setProvider($type->driver);
        }
        return $this->getProvider()->charge($token, $order);
    }

    /**
     * Refund a sale
     *
     * @param string $token
     *
     * @return void
     */
    public function refund($token)
    {
        $transaction = $this->getByHashedId($token);
        $order = $transaction->order;

        if ($order->status == 'refunded') {
            throw new AlreadyRefundedException();
        }

        $result = $this->getProvider()->refund($transaction->transaction_id, $transaction->amount);

        if ($result->success) {
            $data = [
                'transaction_id' => $result->transaction->id,
                'success' => true,
                'amount' => -abs($result->transaction->amount),
                'status' => $result->transaction->type,
                'merchant' => '-',
                'order' => $order,
                'notes' => $result->message
            ];
        } else {
            $data = [
                'transaction_id' => $result->params['id'],
                'success' => false,
                'amount' => $result->params['transaction']['amount'],
                'status' => 'error',
                'merchant' => $result->params['merchantId'],
                'order' => $order,
                'notes' => ''
            ];
        }

        $refund = $this->createTransaction($data);

        // Add up each transaction that isn't voided or refunded and successful.

        // TODO Improve the way this is checked...
        if ($order->transactions()->charged()->count() === 1 && $order->total === $transaction->amount) {
            $order->status = 'refunded';
        }

        $transaction->status = 'refunded';
        $order->save();
        $transaction->save();

        return $refund;
    }

    /**
     * Creates a transaction
     *
     * @param array $data
     *
     * @return Transaction
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
     * Voids a transaction
     *
     * @param string $transactionId
     *
     * @return Transaction
     */
    public function void($transactionId)
    {
        $transaction = $this->getByHashedId($transactionId);
        $order = $transaction->order;

        $result = $this->getProvider()->void($transaction->transaction_id);

        $transaction->success = $result->success;
        $transaction->status = 'voided';

        if (!$result->success) {
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
