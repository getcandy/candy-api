<?php

namespace GetCandy\Api\Core\Baskets\Factories;

use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Baskets\Interfaces\BasketLineInterface;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;

class BasketLineFactory implements BasketLineInterface
{
    /**
     * The basket line.
     *
     * @var BasketLine
     */
    protected $line;

    /**
     * The variant factory.
     *
     * @var ProductVariantInterface
     */
    protected $variantFactory;

    public function __construct(ProductVariantInterface $factory)
    {
        $this->variantFactory = $factory;
    }

    /**
     * Initialise the factory.
     *
     * @param BasketLine $line
     * @return BasketLineFactory
     */
    public function init(BasketLine $line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Get the basket line.
     *
     * @return BasketLine
     */
    public function get()
    {
        $variant = $this->variantFactory
            ->init($this->line->variant)
            ->get($this->line->quantity);

        // $this->line->original_cost = $this->line->price;
        $this->line->total_cost = $variant->total_price;
        $this->line->total_tax = $variant->total_tax;
        $this->line->unit_cost = $variant->unit_cost;
        $this->line->unit_tax = $variant->unit_tax;
        $this->line->unit_qty = $variant->unit_qty;
        $this->line->base_cost = $variant->base_cost;

        return $this->line;
    }
}
