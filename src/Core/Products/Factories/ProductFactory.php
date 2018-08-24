<?php

namespace GetCandy\Api\Core\Products\Factories;

use Illuminate\Support\Collection;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;

class ProductFactory implements ProductInterface
{
    /**
     * The product.
     *
     * @var Product
     */
    protected $product;

    /**
     * The variant factory.
     *
     * @var ProductVariantInterface
     */
    protected $variantFactory;

    public function __construct(ProductVariantInterface $variantFactory)
    {
        $this->variantFactory = $variantFactory;
    }

    public function init($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get the processed product.
     *
     * @return void
     */
    public function get()
    {
        foreach ($this->product->variants as $variant) {
            $variant = $this->variantFactory->init($variant)->get();

            if (! $this->product->min_price || $variant->unit_cost < $this->product->min_price) {
                $this->product->min_price = $variant->unit_cost;
                $this->product->min_price_tax = $variant->unit_tax;
            }
            if (! $this->product->max_price || $variant->unit_cost > $this->product->max_price) {
                $this->product->max_price_tax = $variant->unit_tax;
                $this->product->max_price = $variant->unit_cost;
            }
        }

        return $this->product;
    }

    /**
     * Process a collection of products.
     *
     * @param Collection $products
     * @return Collection
     */
    public function collection(Collection $products)
    {
        foreach ($products as $product) {
            $this->init($product)->get();
        }

        return $products;
    }
}
