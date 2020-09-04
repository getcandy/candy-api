<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Customers\Models\Customer;

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

$factory->define(Customer::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'contact_number' => $faker->phoneNumber,
        'alt_contact_number' => $faker->phoneNumber,
        'vat_no' => $faker->randomNumber,
        'company_name' => $faker->company,
        'fields' => [],
    ];
});
