<?php

namespace GetCandy\Api\Core\Payments;

use GetCandy\Api\Core\Payments\Models\Transaction;

class PaymentResponse
{
    /**
     * Whether the payment was successful.
     *
     * @var bool
     */
    public $success = false;

    /**
     * The response message.
     *
     * @var string
     */
    public $message = null;

    /**
     * Any errors that occured.
     *
     * @var array
     */
    public $errors = [];

    /**
     * The transaction object.
     *
     * @var Transaction
     */
    protected $transaction = null;

    public function __construct($success, $message = null, $errors = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->errors = $errors;
    }

    /**
     * Set the transaction.
     *
     * @param Transaction $transaction
     * @return PaymentResponse
     */
    public function transaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get the transaction object.
     *
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
