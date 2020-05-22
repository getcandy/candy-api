<?php

use Faker\Generator as Faker;

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

$factory->define(GetCandy\Api\Core\Products\Models\Product::class, function (Faker $faker) {
    return [
        'attribute_data' => [
            'name' => [
                'webstore' => [
                    'en' => $faker->name,
                ],
            ],
        ],
    ];
});
