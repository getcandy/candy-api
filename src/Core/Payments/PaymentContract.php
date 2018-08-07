<?php

namespace GetCandy\Api\Core\Payments;

interface PaymentContract
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public function driver($driver = null);
}
