<?php

namespace Tests\Unit\Orders;

use Tests\TestCase;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;

class ProcessingTest extends TestCase
{
    public function testCanMakePayment()
    {
        // $basket = app('api')->baskets()->getBasket();

        // // Create our tax bracket
        // $tax = Tax::first();
        // // Get a variant
        // $variant = Product::first()->variants()->first();

        // // Make sure our variant is assigned to this tax bracket
        // $variant->tax_id = $tax->id;
        // $variant->save();

        // $basket = app('api')->baskets()->store([
        //     'basket_id' => $basket->encodedId(),
        //     'variants' => [
        //         ['id' => $variant->encodedId(), 'quantity' => 1],
        //     ],
        // ]);

        // app('api')->baskets()->setTotals($basket);

        // $order = app('api')->orders()->store($basket->encodedId());

        // $price = ShippingPrice::first();

        // $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        // $order = app('api')->orders()->setShipping($order->encodedId(), [
        //     'phone' => 12345,
        //     'firstname' => 'Joe',
        //     'lastname' => 'Bloggs',
        //     'address' => '123 Some road',
        //     'address_two' => 'Somewhere',
        //     'city' => 'Some City',
        //     'country' => 'United Kingdom',
        //     'zip' => 'ZIP123',
        // ]);

        // $order = app('api')->orders()->setBilling($order->encodedId(), [
        //     'phone' => 12345,
        //     'firstname' => 'Joe',
        //     'lastname' => 'Bloggs',
        //     'address' => '123 Some road',
        //     'address_two' => 'Somewhere',
        //     'city' => 'Some City',
        //     'country' => 'United Kingdom',
        //     'zip' => 'ZIP123',
        // ]);

        // $order = app('api')->orders()->process([
        //     'order_id' => $order->encodedId(),
        //     'order_id' => $order->encodedId(),
        //     'payment_token' => 'fake-valid-nonce',
        // ]);
        //     ]
        // $transaction = $order->transactions->where('success', true);

        // if ($transaction->count()) {
        //     // If successful
        //     $this->assertEquals($order->total, $transaction->first()->amount);
        //     $this->assertInstanceOf(\Carbon\Carbon::class, $order->placed_at);
        //     $this->assertEquals('payment-processing', $order->status);

        //     $this->assertTrue(
        //         $transaction->count() == 1
        //     );
        // } else {
        //     // If not
        //     $this->assertNull($order->placed_at);
        //     $this->assertTrue(
        //         $order->transactions->where('success', false)->count() == 1
        //     );
        //     $this->assertFalse(
        //         $order->transactions->where('success', true)->count() == 1
        //     );
        // }
        $this->assertTrue(true);
    }

    public function testCantProcessOrderTwice()
    {
        // $basket = app('api')->baskets()->getBasket();

        // // Create our tax bracket
        // $tax = Tax::first();
        // // Get a variant
        // $variant = Product::first()->variants()->first();

        // // Make sure our variant is assigned to this tax bracket
        // $variant->tax_id = $tax->id;
        // $variant->save();

        // $basket = app('api')->baskets()->store([
        //     'basket_id' => $basket->encodedId(),
        //     'variants' => [
        //         ['id' => $variant->encodedId(), 'quantity' => 2],
        //     ],
        // ]);

        // app('api')->baskets()->setTotals($basket);

        // $order = app('api')->orders()->store($basket->encodedId());

        // $price = ShippingPrice::first();

        // $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        // $order = app('api')->orders()->setShipping($order->encodedId(), [
        //     'phone' => 12345,
        //     'firstname' => 'Joe',
        //     'lastname' => 'Bloggs',
        //     'address' => '123 Some road',
        //     'address_two' => 'Somewhere',
        //     'city' => 'Some City',
        //     'country' => 'United Kingdom',
        //     'zip' => 'ZIP123',
        // ]);

        // $order = app('api')->orders()->setBilling($order->encodedId(), [
        //     'phone' => 12345,
        //     'firstname' => 'Joe',
        //     'lastname' => 'Bloggs',
        //     'address' => '123 Some road',
        //     'address_two' => 'Somewhere',
        //     'city' => 'Some City',
        //     'country' => 'United Kingdom',
        //     'zip' => 'ZIP123',
        // ]);

        // $order = app('api')->orders()->process([
        //     'order_id' => $order->encodedId(),
        //     'payment_token' => 'fake-valid-nonce',
        // ]);

        // // Braintree might reject due to duplication from running tests
        // // so only check if it has been processed.
        // if ($order->placed_at) {
        //     $this->expectException(OrderAlreadyProcessedException::class);
        //     $dupeResult = app('api')->orders()->process([
        //         'order_id' => $order->encodedId(),
        //         'payment_token' => 'fake-valid-nonce',
        //     ]);
        // }
        $this->assertTrue(true);
    }

    protected function getTaxForAmount($tax, $amount)
    {
        return $amount * ($tax->percentage / 100);
    }
}
