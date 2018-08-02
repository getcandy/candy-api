<?php

namespace GetCandy\Api\Core\Products\Factories;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;

class ProductFactory implements ProductInterface
{
    /**
     * The product
     *
     * @var Product
     */
    protected $product;

    /**
     * The variant factory
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
     * Get the processed product
     *
     * @return void
     */
    public function get()
    {
        foreach ($this->product->variants as $variant) {
            $variant = $this->variantFactory->init($variant)->get();
        }
        return $this->product;
    }
}
