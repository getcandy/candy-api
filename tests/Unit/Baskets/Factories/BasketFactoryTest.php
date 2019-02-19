<?php

namespace Tests\Unit\Orders\Services;

use Tests\TestCase;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Discounts\Models\DiscountReward;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;

/**
 * @group baskets
 */
class BasketFactoryTest extends TestCase
{
    public function test_instance_can_be_swapped()
    {
        $current = $this->app->make(BasketFactoryInterface::class);

        $this->assertInstanceOf(BasketFactory::class, $current);
        $this->app->instance(BasketFactoryInterface::class, new \stdClass);
        $swapped = $this->app->make(BasketFactoryInterface::class);

        $this->assertInstanceOf(\stdClass::class, $swapped);
    }

    public function test_can_be_initialised_with_a_basket()
    {
        $basket = $this->getinitalbasket();

        $factory = $this->app->make(BasketFactory::class);

        $basket = $factory->init($basket)->get();

        $this->assertInstanceOf(Basket::class, $basket);
    }

    public function test_basket_gets_hydrated()
    {
        $basket = $this->getinitalbasket();

        $factory = $this->app->make(BasketFactory::class);

        $subTotal = 0;
        $taxTotal = 0;

        $variantFactory = $this->app->make(ProductVariantFactory::class);

        // Work out what we think it should be
        foreach ($basket->lines as $line) {
            $variant = $variantFactory->init($line->variant)->get();
            $subTotal += $variant->unit_cost;
            $taxTotal += $variant->unit_tax;
        }

        $total = $subTotal + $taxTotal;

        $factory->init($basket)->get();

        $this->assertEquals($basket->sub_total, $subTotal);
        $this->assertEquals($basket->total_tax, $taxTotal);
        $this->assertEquals($basket->total_cost, $total);
    }

    /**
     * @group new
     */
    public function test_discount_can_be_added_to_basket()
    {
        $variant = ProductVariant::first();
        $basket = Basket::forceCreate([
            'currency' => 'GBP',
        ]);

        BasketLine::forceCreate([
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

        $basket = $this->app->make(BasketFactory::class)->init($basket)->get();

        $this->assertCount(1, $basket->discounts);

        $this->assertEquals(1, $basket->discount_total);
        $this->assertEquals(10, $basket->sub_total);
        $this->assertEquals(1.80, $basket->total_tax);
        $this->assertEquals(10.80, $basket->total_cost);
    }
}
