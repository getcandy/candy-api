<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
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

$factory->define(AttributeGroup::class, function (Faker $faker) {
    $name = $faker->domainWord;
    return [
        'name' => [
            'en' => $name,
        ],
        'handle' => Str::slug($name),
        'position' => 1,
    ];
});
