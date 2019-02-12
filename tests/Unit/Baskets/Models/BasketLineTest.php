<?php

namespace Tests\Unit\Orders\Models;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\ProductVariant;

/**
 * @group models
 */
class BasketLineTest extends TestCase
{
    public function test_variant_relation_returns_variant()
    {
        $basket = $this->getinitalbasket();
        foreach ($basket->lines as $line) {
            $this->assertInstanceOf(ProductVariant::class, $line->variant);
        }
    }
}
