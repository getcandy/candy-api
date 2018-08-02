<?php

namespace GetCandy\Api\Core\Baskets\Factories;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Discounts\Factory;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Discounts\DiscountInterface;
use GetCandy\Api\Core\Products\ProductVariantInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketLineInterface;

class BasketFactory implements BasketInterface
{
    /**
     * The current basket
     *
     * @var Basket
     */
    protected $basket;

    /**
     * The order attached to the basket
     *
     * @var Order
     */
    protected $order;

    /**
     * The applied discounts
     *
     * @var array
     */
    protected $discounts = [];

    /**
     * The discount factory
     *
     * @var DiscountInterface
     */
    protected $discountFactory;

    /**
     * The basket lines
     *
     * @var mixed
     */
    protected $lines = [];

    /**
     * The basket line factory
     *
     * @var string
     */
    protected $lineFactory;

    public function __construct(
        DiscountInterface $discountFactory,
        BasketLineInterface $lineFactory
    ) {
        $this->lineFactory     = $lineFactory;
        $this->lines           = collect($this->lines);
        $this->discountFactory = $discountFactory;
        $this->discounts       = collect($this->discounts);
    }

    /**
     * Initialise with the basket
     *
     * @param Basket $basket
     * @return BasketFactory
     */
    public function init(Basket $basket)
    {
        $this->basket    = $basket;
        $this->order     = $basket->order;
        $this->discounts = $this->discountFactory
                            ->init($basket->discounts)
                            ->setBasket($this->basket)
                            ->setUser($this->basket->user)
                            ->getApplied();

        foreach ($basket->lines as $line) {
            $this->lines->push(
                $this->lineFactory->init($line)->get()
            );
        }

        return $this;
    }

    /**
     * Set the basket totals
     *
     * @return BasketFactory
     */
    public function get()
    {
        $this->basket->sub_total = 0;
        $this->basket->total_tax = 0;

        foreach ($this->lines as $line) {
            $this->basket->sub_total += $line->total_cost;
            $this->basket->total_tax += $line->total_tax;
        }

        $this->basket->total_cost = $this->basket->sub_total + $this->basket->total_tax;

        return $this->basket;
    }
}
