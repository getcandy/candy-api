<?php

namespace GetCandy\Api\Core\Reports;

use Illuminate\Support\Manager;
use GetCandy\Api\Core\Reports\Providers\Sales;
use GetCandy\Api\Core\Reports\Providers\Orders;
use GetCandy\Api\Core\Reports\Providers\Products;
use GetCandy\Api\Core\Reports\Providers\Shipping;
use GetCandy\Api\Core\Reports\Providers\Attributes;
use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;

class ReportManager extends Manager implements ReportManagerContract
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver = null)
    {
        return $this->driver($driver);
    }

    /**
     * Create the Sales driver.
     *
     * @return Sales
     */
    public function createSalesDriver()
    {
        return $this->buildProvider(
            Sales::class
        );
    }

    /**
     * Create the Sales driver.
     *
     * @return Orders
     */
    public function createOrdersDriver()
    {
        return $this->buildProvider(
            Orders::class
        );
    }

    /**
     * Create the Sales driver.
     *
     * @return Shipping
     */
    public function createShippingDriver()
    {
        return $this->buildProvider(
            Shipping::class
        );
    }

    /**
     * Create the Products driver.
     *
     * @return Products
     */
    public function createProductsDriver()
    {
        return $this->buildProvider(
            Products::class
        );
    }

    /**
     * Create the Products driver.
     *
     * @return Products
     */
    public function createAttributesDriver()
    {
        return $this->buildProvider(
            Attributes::class
        );
    }


    /**
     * Build a layout provider instance.
     *
     * @param  string  $provider
     * @param  array  $config
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
        return 'sales';
    }
}
