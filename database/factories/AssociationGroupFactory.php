<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Associations\Models\AssociationGroup;
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

$factory->define(AssociationGroup::class, function (Faker $faker) {
    $name = $faker->unique()->company;

    return [
        'name' => $name,
        'handle' => Str::slug($name),
    ];
});
