<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Orders\Models\Order;

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

$factory->define(Order::class, function (Faker $faker) {
    return [
        'sub_total' => $faker->randomNumber,
        'delivery_total' => $faker->randomNumber,
        'discount_total' => $faker->randomNumber,
        'tax_total' => $faker->randomNumber,
        'order_total' => $faker->randomNumber,
        'status' => 'awaiting-payment',
        'currency' => 'GBP',
        'conversion' => 1,
    ];
});
