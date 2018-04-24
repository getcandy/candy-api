<?php

namespace Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Discounts\Models\DiscountReward;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;

class DiscountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $discount = Discount::forceCreate([
            'attribute_data' => [
                'name' => [
                    'en' => 'Foo 10 Percent',
                ],
            ],
            'uses' => 0,
            'status' => 1,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addYear(1),
        ]);

        $set = DiscountCriteriaSet::forceCreate([
            'discount_id' => $discount->id,
            'scope' => 'all',
            'outcome' => 1,
        ]);

        $item = DiscountCriteriaItem::forceCreate([
            'discount_criteria_set_id' => $set->id,
            'type' => 'coupon',
            'value' => 'FOO10',
        ]);

        $reward = DiscountReward::forceCreate([
            'discount_id' => $discount->id,
            'type' => 'percentage',
            'value' => 10,
        ]);

        $discount = Discount::forceCreate([
            'attribute_data' => [
                'name' => [
                    'en' => 'Free Shipping',
                ],
            ],
            'uses' => 0,
            'status' => 1,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addYear(1),
        ]);

        $set = DiscountCriteriaSet::forceCreate([
            'discount_id' => $discount->id,
            'scope' => 'all',
            'outcome' => 1,
        ]);

        $item = DiscountCriteriaItem::forceCreate([
            'discount_criteria_set_id' => $set->id,
            'type' => 'coupon',
            'value' => 'FREE_SHIPPING',
        ]);

        $reward = DiscountReward::forceCreate([
            'discount_id' => $discount->id,
            'type' => 'free_shipping',
            'value' => 10,
        ]);
    }
}
