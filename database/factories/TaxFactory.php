<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Taxes\Models\Tax;

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

$factory->define(Tax::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'percentage' => $faker->numberBetween(0, 20),
        'default' => $faker->boolean,
    ];
});
