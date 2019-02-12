<?php

namespace Tests\Unit\Products\Factories;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;

/**
 * @group products
 */
class ProductVariantFactoryTest extends TestCase
{
    public function test_can_get_variant_guest_pricing()
    {
        $variant = ProductVariant::first();

        $this->assertNull($variant->unit_cost);
        $factory = $this->app->make(ProductVariantFactory::class);
        $factory->init($variant);
        $result = $factory->get();
        $this->assertEquals($result->unit_cost, $variant->price);
    }
}
