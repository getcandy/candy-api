<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use GetCandy\Api\Core\Attributes\Models\Attribute;
use Faker\Generator as Faker;
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

$factory->define(Attribute::class, function (Faker $faker) {
    $name = $faker->word;
    $type = $faker->randomElement(
        ['text', 'textarea', 'select', 'richtext']
    );
    return [
        'name' => [
            'en' => $name,
        ],
        'handle' => Str::slug($name),
        'position' => 1,
        'variant' => $faker->boolean,
        'searchable' => $faker->boolean,
        'filterable' => $faker->boolean,
        'system' => $faker->boolean,
        'channeled' => $faker->boolean,
        'translatable' => $faker->boolean,
        'required' => $faker->boolean,
        'type' => $faker->randomElement(
            ['text', 'textarea', 'select', 'richtext']
        ),
        'lookups' => $type == 'select' ? [1,2,3,4,5] : [],
    ];
});
