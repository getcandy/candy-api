<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Countries\Models\Country;

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

$factory->define(Country::class, function (Faker $faker) {
    return [
        'name' => $faker->country,
        'region' => 'CandyLand',
        'sub_region' => 'CandyLand Sub Region',
        'iso_a_2' => 'CAND',
        'iso_a_3' => 'CANDYL',
        'iso_numeric' => 324,
    ];
});
