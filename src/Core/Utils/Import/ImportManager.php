<?php

namespace GetCandy\Api\Core\Utils\Import;

use GetCandy\Api\Core\Utils\Import\Providers\Product;
use Illuminate\Support\Manager;

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
     * @return \GetCandy\Api\Core\Utils\Import\Providers\Product
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
     * @return mixed
     */
    public function buildProvider($provider)
    {
        return $this->app->make($provider);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'product';
    }
}
