<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use GetCandy\Api\Core\Currencies\Models\Currency;

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

$factory->define(Currency::class, function (Faker $faker) {
    $name = $faker->unique()->company;

    return [
        'name' => ucfirst($name),
        'code' => Str::slug($name),
        'enabled' => $faker->boolean,
        'format' => '£{price}',
        'exchange_rate' => 1,
        'decimal_point' => '.',
        'thousand_point' => ',',
        'default' => $faker->boolean,
    ];
});
