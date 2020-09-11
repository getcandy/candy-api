<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Routes\Models\Route;
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

$factory->define(Route::class, function (Faker $faker) {
    return [
        'element_type' => Product::class,
        'element_id' => 1,
        'slug' => Str::slug($faker->word),
        'path' => Str::slug($faker->word),
        'default' => $faker->boolean,
    ];
});
