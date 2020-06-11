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
     * @var string|null
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
     * @var null|\GetCandy\Api\Core\Payments\Models\Transaction
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
     * @param  \GetCandy\Api\Core\Payments\Models\Transaction  $transaction
     * @return $this
     */
    public function transaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get the transaction object.
     *
     * @return \GetCandy\Api\Core\Payments\Models\Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
