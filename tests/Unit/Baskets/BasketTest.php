<?php

namespace Tests\Unit\Baskets;

use Carbon\Carbon;
use TaxCalculator;
use Tests\TestCase;
use Tests\Stubs\User;
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

    public function testBasketTotalIsInCents()
    {
        $basket = app('api')->baskets()->getBasket();
        $basketId = $basket->encodedId();

        $this->assertTrue($basket instanceof Basket);

        $variant = Product::first()->variants()->first();

        $basket = app('api')->baskets()->store([
            'basket_id' => $basket->encodedId(),
            'variants' => [
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        $basket->refresh();

        $this->assertEquals($variant->price, $basket->subTotal);

        $this->assertTrue($basket->encodedId() === $basketId);
    }

    public function testGetNewBasketAfterOrder()
    {
        // Get our user and "login"
        $user = User::find(7);
        $this->actingAs(
            $user
        );

        // Get a basket
        $basket = app('api')->baskets()->getBasket(null, $user);

        $firstBasketId = $basket->id;

        // Create our tax bracket
        $tax = app('api')->taxes()->getDefaultRecord();

        // Get a variant
        $variant = Product::first()->variants()->first();

        // Make sure our variant is assigned to this tax bracket
        $variant->tax_id = $tax->id;
        $variant->save();

        $basket = app('api')->baskets()->store([
            'basket_id' => $basket->encodedId(),
            'variants' => [
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        // Make an order
        $order = app('api')->orders()->store($basket->encodedId());

        // Set the orders placed at
        $order->placed_at = Carbon::now();
        $order->save();

        // Get a basket for the user again
        $basket = app('api')->baskets()->getBasket(null, $user);

        // Make sure it's different
        $this->assertFalse($basket->id == $firstBasketId);
    }

    public function testCanGetBasketWithoutTax()
    {
        TaxCalculator::setTax();

        $basket = app('api')->baskets()->getBasket();

        $firstBasketId = $basket->id;

        // Create our tax bracket
        $tax = app('api')->taxes()->getDefaultRecord();

        // Get a variant
        $variant = Product::first()->variants()->first();

        // Make sure our variant is assigned to this tax bracket
        $variant->tax_id = $tax->id;
        $variant->save();

        $basket = app('api')->baskets()->store([
            'basket_id' => $basket->encodedId(),
            'variants' => [
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        app('api')->baskets()->setTotals($basket);

        $this->assertEquals($variant->price, $basket->total);
        $this->assertEquals(0, $basket->tax);
    }
}
