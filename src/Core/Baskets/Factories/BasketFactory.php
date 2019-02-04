<?php

namespace GetCandy\Api\Core\Baskets\Factories;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Events\BasketFetchedEvent;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketLineInterface;

class BasketFactory implements BasketFactoryInterface
{
    /**
     * The current basket.
     *
     * @var Basket
     */
    protected $basket;

    /**
     * The order attached to the basket.
     *
     * @var Order
     */
    protected $order;

    /**
     * The basket lines.
     *
     * @var BasketLineInterface
     */
    public $lines;

    public function __construct(
        BasketLineInterface $lineFactory
    ) {
        $this->lines = $lineFactory;
    }

    /**
     * Initialise with the basket.
     *
     * @param Basket $basket
     * @return BasketFactory
     */
    public function init(Basket $basket)
    {
        $this->basket = $basket;
        $this->order = $basket->order;

        foreach ($basket->discounts as $discount) {
            $this->lines->discount($discount);
        }
        $this->lines->add($basket->lines);

        return $this;
    }

    /**
     * Set the basket totals.
     *
     * @return BasketFactory
     */
    public function get()
    {
        $this->basket->sub_total = 0;
        $this->basket->total_tax = 0;
        $this->basket->total_cost = 0;

        foreach ($this->lines->get() as $line) {
            $this->basket->sub_total += $line->total_cost;
            $this->basket->total_tax += $line->total_tax;
        }

        $this->basket->discount_total = $this->basket->lines->sum('discount_total');

        $this->basket->total_cost = $this->basket->sub_total + $this->basket->total_tax;

        event(new BasketFetchedEvent($this->basket));

        return $this->basket;
    }

    /**
     * Clone the basket.
     *
     * @return Basket
     */
    public function clone()
    {
        $clone = $this->basket->replicate();

        $clone->save();

        foreach ($clone->lines as $line) {
            $cloned = $line->replicate();
            $cloned->basket()->associate($clone);
            $cloned->save();
        }

        return $clone;
    }
}
