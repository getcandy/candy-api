<?php

namespace Tests\Unit\Orders;

use TaxCalculator;
use Tests\TestCase;
use CurrencyConverter;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;

class BuildingTest extends TestCase
{
    /**
     * @group failing
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
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        $basket->refresh();

        $taxAmount = $this->getTaxForAmount($tax, $variant->price);

        $this->assertEquals($basket->tax, $taxAmount);
        $this->assertEquals($basket->subTotal, $variant->price);

        $order = app('api')->orders()->store($basket->encodedId());

        $this->assertTrue($order->status == 'awaiting-payment');

        $this->assertTrue($order->lines->count() == 1);

        $this->assertEquals($basket->subTotal, $order->sub_total);
        $this->assertEquals($basket->total, $order->order_total);

        foreach ($order->lines as $line) {
            $this->assertTrue($line->tax == $this->getTaxForAmount($tax, $line->line_amount));
            $this->assertTrue(! $line->discount);
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
                ['id' => $variantTwo->encodedId(), 'quantity' => 1],
            ],
        ]);

        app('api')->baskets()->setTotals($basket);

        $taxAmount = $this->getTaxForAmount($tax, $variantOne->price + $variantTwo->price);

        $this->assertEquals($basket->tax, $taxAmount);

        $this->assertEquals($basket->subTotal, $variantOne->price + $variantTwo->price);

        $order = app('api')->orders()->store($basket->encodedId());

        $this->assertTrue($order->lines->count() == 2);

        foreach ($order->lines as $line) {
            $this->assertEquals($line->tax, $this->getTaxForAmount($tax, $line->line_amount));
            $this->assertTrue(! $line->discount);
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
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        app('api')->baskets()->setTotals($basket);

        $order = app('api')->orders()->store($basket->encodedId());

        // Add some shipping
        $price = ShippingPrice::first();

        $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        $this->assertTrue($order->lines->count() == 2);

        $shipping = $order->lines->where('is_shipping', 1);

        // Make sure we only have one shipping line
        $this->assertTrue($shipping->count() == 1);

        // Make sure the shipping cost is the same as the original price
        $this->assertTrue($shipping->first()->line_amount == $price->rate);

        // Make sure the line tax is correct
        $suggestedTax = $this->getTaxForAmount($tax, $price->rate);
        $this->assertTrue($shipping->first()->tax == $suggestedTax);
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
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        app('api')->baskets()->setTotals($basket);

        // Make an order
        $order = app('api')->orders()->store($basket->encodedId());

        // Make sure shipping has no tax added too.
        $price = ShippingPrice::first();
        $order = app('api')->orders()->addShippingLine($order->encodedId(), $price->encodedId());

        $this->assertTrue($order->lines->count() == 2);

        $shipping = $order->lines->where('is_shipping', 1)->first();

        $this->assertEquals($price->value, $shipping->line_total + $shipping->tax_total);

        $this->assertEquals($order->subTotal, $order->total);
    }

    /**
     * @group failing
     *
     * @return void
     */
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
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        app('api')->baskets()->setTotals($basket);

        $order = app('api')->orders()->store($basket->encodedId());

        $this->assertEquals($order->sub_total, (int) ($variant->price * $currency->exchange_rate));
    }

    protected function getTaxForAmount($tax, $amount)
    {
        return $amount * ($tax->percentage / 100);
    }
}
