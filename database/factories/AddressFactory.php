<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Addresses\Models\Address;

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

$factory->define(Address::class, function (Faker $faker) {
    return [
        'salutation' => $faker->title,
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'email' => $faker->safeEmail,
        'phone' => $faker->phoneNumber,
        'company_name' => $faker->company,
        'address' => $faker->streetAddress,
        'address_two' => $faker->streetName,
        'address_three' => $faker->streetName,
        'city' => $faker->city,
        'state' => $faker->state,
        'postal_code' => $faker->postcode,
        'billing' => $faker->boolean,
        'shipping' => $faker->boolean,
        'default' => $faker->boolean,
        'delivery_instructions' => $faker->sentence,
        'last_used_at' => $faker->dateTime,
        'meta' => [],
    ];
});
