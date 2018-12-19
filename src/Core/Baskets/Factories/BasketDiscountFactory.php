<?php

namespace GetCandy\Api\Core\Baskets\Factories;

use GetCandy\Api\Core\Baskets\Interfaces\BasketDiscountFactoryInterface;

class BasketDiscountFactory implements BasketDiscountFactoryInterface
{
    /**
     * The discount pool.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $discounts;

    public function __construct()
    {
        $this->discounts = collect();
    }

    /**
     * Add a discount to the pool.
     *
     * @param string $discount
     * @return void
     */
    public function add($discount)
    {
        $this->discounts->push($discount);

        return $this;
    }

    /**
     * Return all the discounts.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return $this->discounts;
    }
}
