<?php

namespace Tests\Unit;

use TaxCalculator;
use Tests\TestCase;
use PriceCalculator;
use Tests\Stubs\User;
use CurrencyConverter;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;

class OrderTest extends TestCase
{

    /**
     * Tests if a guest order can be created from a basket
     *
     * @return void
     */
    public function testOrderIsCreatedFromBasket()
    {
        $basket = app('api')->baskets()->getBasket();

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
                ['id' => $variant->encodedId(), 'quantity' => 1]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);

        $taxAmount = $this->getTaxForAmount($tax, $variant->price);

        $this->assertTrue($basket->tax == $taxAmount);
        $this->assertTrue($basket->total == $variant->price);

        $order = app('api')->orders()->store($basket->encodedId());

        $this->assertTrue($order->status == 'awaiting-payment');

        $this->assertTrue($order->lines->count() == 1);

        $this->assertTrue($basket->total == $order->subTotal);

        foreach ($order->lines as $line) {
            $this->assertTrue($line->tax == $this->getTaxForAmount($tax, $line->line_amount));
            $this->assertTrue(!$line->discount);
        }
    }

    public function testCanAddMultipleItemsToOrder()
    {
        $basket = app('api')->baskets()->getBasket();

        // Create our tax bracket
        $tax = app('api')->taxes()->getDefaultRecord();

        // Get a variant
        $variantOne = Product::first()->variants()->first();
        $variantTwo = Product::find(2)->variants()->first();

        $basket = app('api')->baskets()->store([
            'basket_id' => $basket->encodedId(),
            'variants' => [
                ['id' => $variantOne->encodedId(), 'quantity' => 1],
                ['id' => $variantTwo->encodedId(), 'quantity' => 1]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);


        $taxAmount = $this->getTaxForAmount($tax, $variantOne->price + $variantTwo->price);

        $this->assertTrue($basket->tax == $taxAmount);

        $this->assertEquals($basket->total, $variantOne->price + $variantTwo->price);

        $order = app('api')->orders()->store($basket->encodedId());

        $this->assertTrue($order->lines->count() == 2);

        foreach ($order->lines as $line) {
            $this->assertEquals($line->tax, $this->getTaxForAmount($tax, $line->line_amount));
            $this->assertTrue(!$line->discount);
        }
    }

    public function testCanAddShippingToOrder()
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
                ['id' => $variant->encodedId(), 'quantity' => 1]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);

        $order = app('api')->orders()->store($basket->encodedId());

        // Add some shipping
        $price = ShippingPrice::first();

        $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        $this->assertTrue($order->lines->count() == 2);

        $shipping = $order->lines->where('shipping', 1);

        // Make sure we only have one shipping line
        $this->assertTrue($shipping->count() == 1);

        // Make sure the shipping cost is the same as the original price
        $this->assertTrue($shipping->first()->line_amount == $price->rate);

        // Make sure the line tax is correct
        $suggestedTax = $this->getTaxForAmount($tax, $price->rate);
        $this->assertTrue($shipping->first()->tax == $suggestedTax);
    }

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
                ['id' => $variant->encodedId(), 'quantity' => 1]
            ]
        ]);

        $basket = app('api')->baskets()->addDiscount(
            $basket->encodedId(),
            'FOO10'
        );

        app('api')->baskets()->setTotals($basket);

        $this->assertTrue($basket->discounts->count() == 1);

        $order = app('api')->orders()->store($basket->encodedId());

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
            if (!$line->shipping) {
                $this->assertEquals(
                    $line->line_amount * ($percentage / 100),
                    $line->discount
                );
            } else {
                $this->assertTrue(!$line->discount);
            }
        }

        // Subtotal, manually calculated
        $this->assertEquals($order->total, $order->subTotal + $order->tax);
    }

    public function testCanMakePayment()
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
                ['id' => $variant->encodedId(), 'quantity' => 1]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);

        $order = app('api')->orders()->store($basket->encodedId());

        $price = ShippingPrice::first();

        $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        $order = app('api')->orders()->setShipping($order->encodedId(), [
            'phone' => 12345,
            'firstname' => 'Joe',
            'lastname' => 'Bloggs',
            'address' => '123 Some road',
            'address_two' => 'Somewhere',
            'city' => 'Some City',
            'country' => 'United Kingdom',
            'zip' => 'ZIP123'
        ]);

        $order = app('api')->orders()->setBilling($order->encodedId(), [
            'phone' => 12345,
            'firstname' => 'Joe',
            'lastname' => 'Bloggs',
            'address' => '123 Some road',
            'address_two' => 'Somewhere',
            'city' => 'Some City',
            'country' => 'United Kingdom',
            'zip' => 'ZIP123'
        ]);

        $order = app('api')->orders()->process([
            'order_id' => $order->encodedId(),
            'order_id' => $order->encodedId(),
            'payment_token' => 'fake-valid-nonce',
        ]);

        $transaction = $order->transactions->where('success', true);

        if ($transaction->count()) {
            // If successful
            $this->assertEquals($order->total, $transaction->first()->amount);
            $this->assertInstanceOf(\Carbon\Carbon::class, $order->placed_at);
            $this->assertEquals('payment-processing', $order->status);

            $this->assertTrue(
                $transaction->count() == 1
            );
        } else {
            // If not
            $this->assertNull($order->placed_at);
            $this->assertTrue(
                $order->transactions->where('success', false)->count() == 1
            );
            $this->assertFalse(
                $order->transactions->where('success', true)->count() == 1
            );
        }
    }

    public function testCantProcessOrderTwice()
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
                ['id' => $variant->encodedId(), 'quantity' => 2]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);

        $order = app('api')->orders()->store($basket->encodedId());

        $price = ShippingPrice::first();

        $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        $order = app('api')->orders()->setShipping($order->encodedId(), [
            'phone' => 12345,
            'firstname' => 'Joe',
            'lastname' => 'Bloggs',
            'address' => '123 Some road',
            'address_two' => 'Somewhere',
            'city' => 'Some City',
            'country' => 'United Kingdom',
            'zip' => 'ZIP123'
        ]);

        $order = app('api')->orders()->setBilling($order->encodedId(), [
            'phone' => 12345,
            'firstname' => 'Joe',
            'lastname' => 'Bloggs',
            'address' => '123 Some road',
            'address_two' => 'Somewhere',
            'city' => 'Some City',
            'country' => 'United Kingdom',
            'zip' => 'ZIP123'
        ]);

        $order = app('api')->orders()->process([
            'order_id' => $order->encodedId(),
            'payment_token' => 'fake-valid-nonce',
        ]);

        $this->expectException(OrderAlreadyProcessedException::class);

        $dupeResult = app('api')->orders()->process([
            'order_id' => $order->encodedId(),
            'payment_token' => 'fake-valid-nonce',
        ]);
    }

    public function testCanGetOrderWithoutTax()
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
                ['id' => $variant->encodedId(), 'quantity' => 1]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);

        // Make an order
        $order = app('api')->orders()->store($basket->encodedId());

        // Make sure shipping has no tax added too.
        $price = ShippingPrice::first();
        $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        $this->assertTrue($order->lines->count() == 2);

        $shipping = $order->lines->where('shipping', 1)->first();

        $this->assertEquals($price->rate, $shipping->line_amount + $shipping->tax);

        $this->assertEquals($order->subTotal, $order->total);
    }

    public function testCanAddEuroOrder()
    {
        CurrencyConverter::set('EUR');

        $basket = app('api')->baskets()->getBasket();
        $currency = app('api')->currencies()->getByCode('EUR');

        // Create our tax bracket
        $tax = app('api')->taxes()->getDefaultRecord();

        // Get a variant
        $variant = Product::first()->variants()->first();

        $basket = app('api')->baskets()->store([
            'basket_id' => $basket->encodedId(),
            'variants' => [
                ['id' => $variant->encodedId(), 'quantity' => 1]
            ]
        ]);

        app('api')->baskets()->setTotals($basket);

        $order = app('api')->orders()->store($basket->encodedId());

        $this->assertEquals($order->subTotal, round($variant->price / $currency->exchange_rate, 2));
    }

    protected function getTaxForAmount($tax, $amount)
    {
        return $amount * ($tax->percentage / 100);
    }
}
