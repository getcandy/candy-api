<?php

use Faker\Generator as Faker;

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

$factory->define(GetCandy\Api\Core\Products\Models\ProductVariant::class, function (Faker $faker) {
    return [
        'sku' => $faker->unique()->slug,
        'stock' => $faker->numberBetween(10,2000),
        'price' => $faker->randomNumber(2),
    ];
});
