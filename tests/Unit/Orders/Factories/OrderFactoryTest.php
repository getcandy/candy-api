<?php

namespace Tests\Unit\Orders\Services;

use Carbon\Carbon;
use Tests\TestCase;
use Tests\Stubs\User;
use Illuminate\Support\Facades\Event;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;
use GetCandy\Api\Core\Orders\Factories\OrderFactory;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Discounts\Models\DiscountReward;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Orders\Interfaces\OrderFactoryInterface;
use GetCandy\Api\Core\Orders\Exceptions\BasketHasPlacedOrderException;

/**
 * @group orders
 */
class OrderFactoryTest extends TestCase
{
    public function test_can_be_instantiated()
    {
        $service = $this->app->make(OrderFactoryInterface::class);
        $this->assertInstanceOf(OrderFactory::class, $service);
    }

    public function test_getters_and_setters_are_working_as_expected()
    {
        $factory = $this->app->make(OrderFactory::class);

        $basket = $this->getinitalbasket();

        $factory->basket($basket);

        $this->assertSame($factory->getBasket(), $basket);
    }

    public function test_order_can_be_created_from_basket()
    {
        $factory = $this->app->make(OrderFactory::class);
        Event::fake();

        $basket = $this->getinitalbasket();

        $order = $factory->basket($basket)->resolve();

        Event::assertDispatched(OrderSavedEvent::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });

        $this->assertEquals($basket->id, $order->basket_id);
        $this->assertEquals($basket->sub_total * 100, $order->sub_total);
        $this->assertEquals($basket->total_cost * 100, $order->order_total);
        $this->assertEquals($basket->discount_total * 100, $order->discount_total);
        $this->assertEquals($basket->total_tax * 100, $order->tax_total);

        $this->assertEquals($order->sub_total + $order->tax_total, $order->order_total);
    }

    public function test_order_can_resolve_with_a_user()
    {
        $user = User::first();
        $factory = $this->app->make(OrderFactory::class);
        Event::fake();

        $basket = $this->getinitalbasket();
        $basket->user()->associate($user);

        $order = $factory->basket($basket)->resolve();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_user_fields_are_set_when_resolved()
    {
        $user = User::first();

        $shippingCheckable = [
            'firstname' => 'Jake',
            'lastname' => 'Perolta',
            'address' => '123 Someroad',
            'address_two' => 'Some lane',
            'address_three' => 'Some place',
            'city' => 'Some City',
            'state' => 'Some State',
            'country' => 'United Kingdom',
            'zip' => 'AB12 3CD',
        ];

        $shipping = Address::forceCreate(array_merge([
            'user_id' => $user->id,
            'shipping' => true,
        ], $shippingCheckable));

        $billingCheckable = [
            'firstname' => 'Boyle',
            'lastname' => 'Perolta',
            'address' => '123 Another road',
            'address_two' => 'Another lane',
            'address_three' => 'Another place',
            'city' => 'Another City',
            'state' => 'Another State',
            'country' => 'United Kingdom',
            'zip' => 'EF45 6GH',
        ];

        $billing = Address::forceCreate(array_merge([
            'user_id' => $user->id,
            'billing' => true,
        ], $billingCheckable));

        $factory = $this->app->make(OrderFactory::class);
        Event::fake();

        $basket = $this->getinitalbasket();
        $basket->user()->associate($user);

        $order = $factory->basket($basket)->resolve();

        foreach ($shippingCheckable as $key => $value) {
            $this->assertEquals($value, $order->getAttribute('shipping_'.$key));
        }

        foreach ($billingCheckable as $key => $value) {
            $this->assertEquals($value, $order->getAttribute('billing_'.$key));
        }
    }

    public function test_user_can_be_set_manually()
    {
        $user = User::first();
        $factory = $this->app->make(OrderFactory::class);
        $basket = $this->getinitalbasket();

        $factoryUser = $factory->basket($basket)->getUser();

        $this->assertNull($factoryUser);

        $factoryUser = $factory->user($user)->getUser();

        $this->assertSame($user, $factoryUser);
    }

    public function test_can_set_null_user()
    {
        $factory = $this->app->make(OrderFactory::class);

        $factoryUser = $factory->user(null)->getUser();

        $this->assertNull($factoryUser);
    }

    public function test_user_is_set_from_basket()
    {
        $user = User::first();
        $factory = $this->app->make(OrderFactory::class);
        $basket = $this->getinitalbasket();

        $basket->user()->associate($user);

        $factoryUser = $factory->basket($basket)->getUser();

        $this->assertSame($user, $factoryUser);
    }

    public function test_order_lines_are_created_correctly()
    {
        $factory = $this->app->make(OrderFactory::class);
        Event::fake();

        $basket = $this->getinitalbasket();

        $order = $factory->basket($basket)->resolve();

        foreach ($order->lines as $line) {
            $basketLine = $basket->lines->first(function ($bLine) use ($line) {
                return $bLine->product_variant_id == $line->product_variant_id;
            });
            $this->assertEquals($basketLine->total_cost * 100, $line->line_total);
            $this->assertEquals($basketLine->unit_cost * 100, $line->unit_price);
            $this->assertEquals($basketLine->total_tax * 100, $line->tax_total);
        }
    }

    public function test_cannot_update_order_if_processed()
    {
        $factory = $this->app->make(OrderFactory::class);
        Event::fake();

        $basket = $this->getinitalbasket();

        $order = $factory->basket($basket)->resolve();

        $order->update([
            'placed_at' => Carbon::now(),
        ]);

        $this->assertEquals($basket->id, $order->id);
        $this->assertNotNull($order->placed_at);

        $basket->refresh();

        $this->expectException(BasketHasPlacedOrderException::class);
        $factory->basket($basket)->resolve();
    }

    public function test_percentage_coupon_can_be_set()
    {
        $factory = $this->app->make(OrderFactory::class);

        $variant = \GetCandy\Api\Core\Products\Models\ProductVariant::first();
        $basket = \GetCandy\Api\Core\Baskets\Models\Basket::forceCreate([
            'currency' => 'GBP',
        ]);


        \GetCandy\Api\Core\Baskets\Models\BasketLine::forceCreate([
            'product_variant_id' => $variant->id,
            'basket_id' => $basket->id,
            'quantity' => 1,
            'total' => $variant->price,
        ]);

        $discount = Discount::forceCreate([
            'attribute_data' => [
                'name' => ['en' => 'Test Discount'],
            ],
            'status' => 1,
            'channel_id' => 1,
            'start_at' => \Carbon\Carbon::now()->startOfDay(),
            'end_at' => \Carbon\Carbon::now()->endOfDay(),
            'lower_limit' => 1,
        ]);

        $criteria = DiscountCriteriaSet::forceCreate([
            'discount_id' => $discount->id,
            'scope' => 'all',
            'outcome' => 1,
        ]);

        DiscountReward::forceCreate([
            'discount_id' => $discount->id,
            'type' => 'percentage',
            'value' => 10,
        ]);

        DiscountCriteriaItem::forceCreate([
            'discount_criteria_set_id' => $criteria->id,
            'type' => 'coupon',
            'value' => 'TESTCOUPON',
        ]);

        \DB::table('basket_discount')->insert([
            'basket_id' => $basket->id,
            'discount_id' => $discount->id,
            'coupon' => 'TESTCOUPON',
        ]);

        $basket = $basket->refresh();

        $this->assertCount(1, $basket->discounts);


        $basket = $this->app->make(BasketFactory::class)->init($basket)->get();


        $order = $factory->basket($basket)->resolve();

        // dump($basket->total_cost, $basket->total_tax);
        // dd($basket->discount_total, $basket->sub_total, $basket->sub_total - $basket->discount_total, $order->sub_total);

        $this->assertEquals(($basket->sub_total - $basket->discount_total), $order->sub_total / 100);
        $this->assertEquals($basket->discount_total, $order->discount_total / 100);
        $this->assertEquals($basket->total_tax, $order->tax_total / 100);
        $this->assertEquals($basket->total_cost, $order->order_total / 100);

        foreach ($order->discounts as $discount) {
            $this->assertEquals(100, $discount->amount);
        }
    }

    // public function test_can_add_shipping_to_an_order()
    // {
    //     $factory = $this->app->make(OrderFactory::class);

    //     Event::fake();

    //     $basket = $this->getinitalbasket();

    //     $order = $factory->basket($basket)->resolve();
    // }
}
