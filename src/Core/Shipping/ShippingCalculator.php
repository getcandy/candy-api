<?php

namespace GetCandy\Api\Core\Shipping;

use Illuminate\Support\Manager;
use GetCandy\Api\Core\Shipping\Providers\StandardProvider;
use GetCandy\Api\Core\Shipping\Providers\RegionalProvider;

class ShippingCalculator extends Manager
{
    protected $method;

    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($method)
    {
        $this->method = $method;

        return $this->createDriver($method->type);
    }

    public function createStandardDriver()
    {
        return $this->buildProvider(
            StandardProvider::class
        );
    }

    public function createRegionalDriver()
    {
        return $this->buildProvider(
            RegionalProvider::class
        );
    }

    public function buildProvider($provider, $config = [])
    {
        return new $provider(
            $this->method
        );
    }

    public function getDefaultDriver()
    {
        return $this->with('standard');
    }
}
