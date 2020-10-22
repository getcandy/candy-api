<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;

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

$factory->define(ReusablePayment::class, function (Faker $faker) {
    return [
        'type' => $faker->creditCardType,
        'provider' => 'sagepay',
        'last_four' => $faker->randomNumber(4),
        'token' => '123456789',
        'expires_at' => $faker->dateTime,
    ];
});
