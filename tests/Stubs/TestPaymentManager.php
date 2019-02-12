<?php

namespace Tests\Stubs;

class TestPaymentManager extends AbstractPaymentManager
{
    public function driver($driver = null)
    {
        return new TestPaymentDriver;
    }

    public function with($driver)
    {
        return $this->driver();
    }
}
