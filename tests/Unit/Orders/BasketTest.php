<?php

namespace Tests\Unit;

use Tests\TestCase;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Products\Models\Product;

class BasketTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGuestBasketCanBeCreatedAndRetrieved()
    {
        $basket = app('api')->baskets()->getBasket();
        $basketId = $basket->encodedId();

        $this->assertTrue($basket instanceof Basket);

        $basket = app('api')->baskets()->getBasket($basketId);

        $this->assertTrue($basket->encodedId() === $basketId);
    }
}
