<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;


/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/


$factory->define(Product::class, function (Faker $faker) {
    return [
        'attribute_data' => [
            'name' => [
                'webstore' => [
                    'en' => $faker->name,
                ],
            ],
        ],
    ];
});

$factory->afterCreating(Product::class, function ($product, $faker) {
    // Set up initial variant
    $product->variants()->save(factory(ProductVariant::class)->make());

    $channel = factory(Channel::class)->create();

    dd($channel);
});