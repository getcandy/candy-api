<?php

namespace Tests\Stubs;

use GetCandy\Api\Core\Payments\Providers\AbstractProvider;

class TestPaymentProvider extends AbstractProvider
{
    public function charge()
    {
        return false;
    }

    public function validate($token)
    {
        return false;
    }

    public function getClientToken()
    {
        return 'test';
    }

    public function getName()
    {
        return 'Test Payment Provider';
    }

    public function refund($token, $amount, $description)
    {
        return false;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getToken()
    {
        return $this->token;
    }
}
