<?php

namespace GetCandy\Api\Core\Payments\Providers;

use GetCandy\Api\Core\Orders\Models\Order;

class OnAccount extends AbstractProvider
{
    protected $name = 'On Account';

    public function getName()
    {
        return $this->name;
    }

    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function validateToken($token)
    {
        return true;
    }

    public function getClientToken()
    {
        return 'ONACCOUNT';
    }

    public function updateTransaction($transaction)
    {
        return true;
    }

    public function charge($token, Order $order)
    {
        return true;
    }

    public function refund($token, $amount = null)
    {
        return true;
    }
}
