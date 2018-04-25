<?php

namespace Tests\Unit\Discounts;

use Tests\TestCase;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;

class DiscountTest extends TestCase
{
    public function testCanAddDiscountToOrder()
    {
        $basket = app('api')->baskets()->getBasket();

        // Create our tax bracket
        $tax = Tax::first();
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

        $basket = app('api')->baskets()->addDiscount(
            $basket->encodedId(),
            'FOO10'
        );

        app('api')->baskets()->setTotals($basket);

        $order = app('api')->orders()->store($basket->encodedId());

        $this->assertTrue($basket->discounts->count() == 1);

        $percentage = 0;

        foreach ($basket->discounts as $discount) {
            foreach ($discount->rewards as $reward) {
                if ($reward->type == 'percentage') {
                    $percentage += $reward->value;
                }
            }
        }

        // Make sure each line has the discount applied, except shipping
        foreach ($order->lines as $line) {
            if (! $line->shipping) {
                $this->assertEquals(
                    $line->line_amount * ($percentage / 100),
                    $line->discount
                );
            } else {
                $this->assertTrue(! $line->discount);
            }
        }

        // Subtotal, manually calculated
        $this->assertEquals($order->total, $order->subTotal + $order->tax);
    }

    /**
     * @group now
     * @return void
     */
    public function testCanAddFreeShippingToOrder()
    {
        $basket = app('api')->baskets()->getBasket();

        // Create our tax bracket
        $tax = Tax::first();
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

        $basket = app('api')->baskets()->addDiscount(
            $basket->encodedId(),
            'FREE_SHIPPING'
        );

        app('api')->baskets()->setTotals($basket);

        $this->assertTrue($basket->discounts->count() == 1);

        $order = app('api')->orders()->store($basket->encodedId());

        $price = ShippingPrice::first();

        $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        $this->assertTrue($order->lines->count() == 2);

        // Subtotal, manually calculated
        $shipping = $order->lines->where('shipping', true)->first();

        $this->assertEquals($shipping->line_amount + $shipping->tax, $shipping->discount);
    }
}
