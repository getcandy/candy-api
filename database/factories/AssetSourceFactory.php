<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Assets\Models\AssetSource;

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

$factory->define(AssetSource::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'handle' => $faker->word,
        'disk' => 'public',
        'default' => $faker->boolean,
        'path' => $faker->word,
    ];
});
