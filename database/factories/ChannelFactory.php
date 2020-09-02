<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Channels\Models\Channel;
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

$factory->define(Channel::class, function (Faker $faker) {
    $name = $faker->unique()->company;

    return [
        'name' => ucfirst($name),
        'handle' => Str::slug($name),
    ];
});
