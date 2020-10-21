<?php

namespace GetCandy\Api\Core\Payments;

use GetCandy\Api\Core\Payments\Providers\Braintree;
use GetCandy\Api\Core\Payments\Providers\Offline;
use GetCandy\Api\Core\Payments\Providers\PayPal;
use GetCandy\Api\Core\Payments\Providers\SagePay;
use Illuminate\Support\Manager;

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
     * @return \GetCandy\Api\Core\Payments\Providers\PayPal
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
     * @return \GetCandy\Api\Core\Payments\Providers\SagePay
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
     * @return \GetCandy\Api\Core\Payments\Providers\Braintree
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
     * @return \GetCandy\Api\Core\Payments\Providers\Offline
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
     * @return \GetCandy\Api\Core\Payments\Providers\AbstractProvider
     */
    public function buildProvider($provider)
    {
        return $this->container->make($provider);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDefaultDriver()
    {
        return config('getcandy.payments.gateway');
    }
}
