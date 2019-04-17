<?php

namespace GetCandy\Api\Core\Utils\Import;

use Illuminate\Support\Manager;
use GetCandy\Api\Core\Utils\Import\Providers\Product;

class ImportManager extends Manager implements ImportManagerContract
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
     * @return Product
     */
    public function createProductDriver()
    {
        return $this->buildProvider(
            Product::class
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
        return 'product';
    }
}
