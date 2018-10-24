<?php

namespace GetCandy\Api\Core\Payments\Providers;

use GetCandy\Api\Core\Payments\Models\Transaction;

class Offline extends AbstractProvider
{
    protected $name = 'Offline';

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
        return 'OFFLINE';
    }

    public function updateTransaction($transaction)
    {
        return true;
    }

    public function charge()
    {
        $transaction = new Transaction();
        $transaction->success = true;

        return $transaction;
    }

    public function refund($token, $amount, $description)
    {
        return true;
    }
}
