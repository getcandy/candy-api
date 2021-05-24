<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Orders\Models\OrderLine;

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

$factory->define(OrderLine::class, function (Faker $faker) {
    return [
        'is_shipping' => false,
        'is_manual' => false,
        'quantity' => 1,
        'line_total' => $faker->randomNumber,
        'unit_price' => $faker->randomNumber,
        'discount_total' => $faker->randomNumber,
        'delivery_total' => $faker->randomNumber,
        'tax_total' => $faker->randomNumber,
        'tax_rate' => 20,
        'unit_qty' => 1,
        'sku' => $faker->slug,
        'description' => $faker->sentence,
    ];
});
