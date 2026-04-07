<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Client;
use Faker\Generator as Faker;

$factory->define(Client::class, function (Faker $faker) {
    return [
        'firstname' => $faker->firstNameMale(),
        'lastname' => $faker->lastName(),
        'email' => $faker->email(),
        'password' => Hash::make('password'),
        'address1' => $faker->address(),
        'city' => $faker->city(),
        'state' => $faker->state(),
        'postcode' => $faker->postcode(),
        'country' => $faker->countryCode(),
        'phonenumber' => $faker->e164PhoneNumber(),
        'language' => 'english',
        'status' => 'Active',
        'currency' => 1,
        'uuid' => $faker->uuid(),
        'companyname' => '',
        'address2' => '',
        'securityqid' => '',
        'securityqans' => '',
        'ip' => '',
        'host' => '',
        'datecreated' => \Carbon\Carbon::now(),
        'email_verified' => 1,
        'email_preferences' => '',
    ];
});
