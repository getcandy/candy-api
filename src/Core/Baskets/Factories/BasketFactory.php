<?php

namespace GetCandy\Api\Core\Baskets\Factories;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Events\BasketFetchedEvent;
use GetCandy\Api\Core\Baskets\Interfaces\BasketLineInterface;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;

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

    protected $tax;

    /**
     * Whether the basket has been changed.
     *
     * @var bool
     */
    protected $changed = false;

    public function __construct(
        BasketLineInterface $lineFactory,
        TaxCalculatorInterface $tax
    ) {
        $this->lines = $lineFactory;
        $this->tax = $tax;
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

        // Get exclusions

        $exclusions = $basket->lines->filter(function ($line) {
            return $line->variant->product->exclusions->count();
        });

        $this->basket->hasExclusions = (bool) $exclusions->count();


        return $this;
    }

    /**
     * Whether the basket has been changed.
     *
     * @param bool $bool
     * @return self
     */
    public function changed($bool)
    {
        $this->changed = $bool;

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
        $this->basket->changed = $this->changed;

        // Without tax, without discounts.
        $subTotal = 0;
        // Discount total, without tax.
        $discountTotal = 0;

        // Total is subtotal minus discount total
        // Tax is the amount from taking off the discount total from sub total.

        foreach ($this->lines->get() as $line) {
            $subTotal += $line->total_cost;
            $discountTotal += $line->discount_total;
        }

        $this->basket->sub_total = $subTotal;
        $this->basket->discount_total = $discountTotal;

        $this->basket->total_tax = $this->tax->amount($subTotal - $discountTotal);
        $this->basket->total_cost = ($this->basket->sub_total - $discountTotal) + $this->basket->total_tax;

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
