<?php

namespace GetCandy\Api\Core\Reports;

use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Core\Reports\Providers\Orders;
use GetCandy\Api\Core\Reports\Providers\Sales;
use Illuminate\Support\Manager;

class ReportManager extends Manager implements ReportManagerContract
{
    /**
     * Get a driver instance.
     *
     * @param  string|null  $driver
     * @return mixed
     */
    public function with($driver = null)
    {
        return $this->driver($driver);
    }

    /**
     * Create the Sales driver.
     *
     * @return \GetCandy\Api\Core\Reports\Providers\Sales
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
     * @return \GetCandy\Api\Core\Reports\Providers\Orders
     */
    public function createOrdersDriver()
    {
        return $this->buildProvider(
            Orders::class
        );
    }

    /**
     * Build a layout provider instance.
     *
     * @param  string  $provider
     * @return \GetCandy\Api\Core\Reports\Providers\AbstractProvider
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
        return 'sales';
    }
}
