<?php

namespace Tests\Unit;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;

class OrderTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function testOrderIsCreatedFromBasket()
    {
        $basket = app('api')->baskets()->getBasket();

        $variant = Product::withoutGlobalScopes()->first()->variants()->first();

        $basket = app('api')->baskets()->store([
            'basket_id' => $basket->encodedId(),
            'variants' => [
                ['id' => $variant->encodedId(), 'quantity' => 1]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);
        $this->assertTrue((float) $basket->total == (float) $variant->price);

        $order = app('api')->orders()->store($basket->encodedId());

        dd($order);

    }
}
