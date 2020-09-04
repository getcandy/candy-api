<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;

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

$factory->define(AttributeGroup::class, function (Faker $faker) {
    $name = $faker->unique()->company;
    return [
        'name' => $name,
        'handle' => Str::slug($name),
        'position' => $faker->numberBetween(1, 10),
    ];
});
