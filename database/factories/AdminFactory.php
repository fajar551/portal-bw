<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Admin;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Admin::class, function (Faker $faker) {
    return [
        //
        'name' => $faker->name,
        'username' => $faker->words(10, true),
        'email' => $faker->unique()->safeEmail,
        'uuid' => (string) Str::uuid(),
        'password' => Hash::make('password'),
        'firstname' => 'Admin Test',
        'lastname' => 'CBMS Test',
        'template' => 'admin',
        'notes' => 'DUMY',
        'language' => 'id',
        'disabled' => 0,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
