<?php

namespace GetCandy\Api\Core\Baskets\Factories;

use GetCandy\Api\Core\Baskets\Interfaces\BasketDiscountFactoryInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketLineInterface;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;

class BasketLineFactory implements BasketLineInterface
{
    /**
     * The basket lines.
     *
     * @var \Illuminate\Support\Collection
     */
    public $lines;

    /**
     * The variant factory.
     *
     * @var \GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface
     */
    protected $variantFactory;

    /**
     * The discount factory.
     *
     * @var \GetCandy\Api\Core\Baskets\Interfaces\BasketDiscountFactoryInterface
     */
    protected $discounts;

    /**
     * The tax calculator instance.
     *
     * @var \GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface
     */
    protected $tax;

    public function __construct(
        ProductVariantInterface $factory,
        BasketDiscountFactoryInterface $discounts,
        TaxCalculatorInterface $tax
    ) {
        $this->variantFactory = $factory;
        $this->lines = collect();
        $this->discounts = $discounts;
        $this->tax = $tax;
    }

    /**
     * Add lines to the instance.
     *
     * @param  \Illuminate\Support\Collection  $lines
     * @return $this
     */
    public function add($lines)
    {
        $this->lines = $lines;

        return $this;
    }

    /**
     * Initialise the factory.
     *
     * @param  \GetCandy\Api\Core\Baskets\Models\BasketLine  $line
     * @return $this
     */
    public function init(BasketLine $line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Add a discount to the instance.
     *
     * @param  string  $coupon
     * @return $this
     */
    public function discount($coupon)
    {
        $this->discounts->add($coupon);

        return $this;
    }

    /**
     * Get the basket line.
     *
     * @return \GetCandy\Api\Core\Baskets\Models\BasketLine
     */
    public function get()
    {
        foreach ($this->lines as $line) {
            $variant = $this->variantFactory
                ->init($line->variant)
                ->get($line->quantity);

            $line->total_cost = $variant->total_price;
            $line->total_tax = $variant->total_tax;
            $line->unit_cost = $variant->unit_cost;
            $line->unit_tax = $variant->unit_tax;
            $line->unit_qty = $variant->unit_qty;
            $line->base_cost = $variant->base_cost;

            foreach ($this->discounts->get() as $discount) {
                foreach ($discount->rewards as $reward) {
                    $method = 'apply'.ucfirst($reward->type);
                    if (method_exists($this, $method)) {
                        $line = $this->{$method}($line, $reward);
                    }
                }
            }
        }

        return $this->lines;
    }

    protected function applyPercentage($line, $reward)
    {
        // Get the decimal
        $decimal = $reward->value / 100;
        $amount = $line->total_cost * $decimal;
        $line->discount_total += $amount;

        // Based on the amount, get the tax total
        $tax = $this->tax->amount($amount);

        // Minus off the difference on the line
        $line->total_tax = ($line->total_tax - $tax);
        // $line->total_cost -= $amount;

        return $line;
    }
}
