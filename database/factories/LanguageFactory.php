<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Languages\Models\Language;
use Illuminate\Support\Str;

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

$factory->define(Language::class, function (Faker $faker) {
    $name = $faker->unique()->company;

    return [
        'name' => $name,
        'lang' => ucfirst($name),
        'iso' => Str::slug($name),
        'default' => $faker->boolean,
        'enabled' => $faker->boolean,
    ];
});
