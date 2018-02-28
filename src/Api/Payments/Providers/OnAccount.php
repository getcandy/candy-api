<?php

namespace GetCandy\Api\Payments\Providers;

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

    public function updateTransaction($transaction)
    {
        return true;
    }

    public function charge($token, $order)
    {
        return true;
    }

    public function refund($token, $amount = null)
    {
        return true;
    }
}
