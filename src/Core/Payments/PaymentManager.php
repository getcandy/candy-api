<?php

namespace GetCandy\Api\Core\Payments;

use Illuminate\Support\Manager;
use GetCandy\Api\Core\Payments\Providers\PayPal;
use GetCandy\Api\Core\Payments\Providers\Offline;
use GetCandy\Api\Core\Payments\Providers\SagePay;
use GetCandy\Api\Core\Payments\Providers\Braintree;

class PaymentManager extends Manager implements PaymentContract
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Create the PayPal driver.
     *
     * @return PayPal
     */
    public function createPaypalDriver()
    {
        return $this->buildProvider(
            PayPal::class
        );
    }

    /**
     * Create the sagepay driver.
     *
     * @return SagePay
     */
    public function createSagepayDriver()
    {
        return $this->buildProvider(
            SagePay::class
        );
    }

    /**
     * Create the sagepay driver.
     *
     * @return SagePay
     */
    public function createBraintreeDriver()
    {
        return $this->buildProvider(
            Braintree::class
        );
    }

    /**
     * Create the offline driver.
     *
     * @return Offline
     */
    public function createOfflineDriver()
    {
        return $this->buildProvider(
            Offline::class
        );
    }

    /**
     * Build a layout provider instance.
     *
     * @param  string  $provider
     * @param  array  $config
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    public function buildProvider($provider)
    {
        return $this->app->make($provider);
    }

    /**
     * Get the default driver name.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('getcandy.payments.gateway');
    }
}
