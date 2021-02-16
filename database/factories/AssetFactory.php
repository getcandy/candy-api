<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Assets\Models\Asset;

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

$factory->define(Asset::class, function (Faker $faker) {
    return [
        'location' => $faker->word,
        'kind' => 'image',
        'sub_kind' => 'jpg',
        'width' => $faker->randomNumber,
        'height' => $faker->randomNumber,
        'title' => $faker->word,
        'original_filename' => "{$faker->word}.jpg",
        'caption' => $faker->word,
        'size' => $faker->randomNumber,
        'extension' => 'jpg',
        'filename' => "{$faker->unique()->word}.jpg",
    ];
});
