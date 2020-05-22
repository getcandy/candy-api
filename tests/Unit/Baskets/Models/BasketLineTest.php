<?php

namespace Tests\Unit\Orders\Models;

use GetCandy\Api\Core\Products\Models\ProductVariant;
use Tests\TestCase;

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
