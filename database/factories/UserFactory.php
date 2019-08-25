<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

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

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('secret'),
        'remember_token' => Str::random(10),
        'verified' => $verified = $faker->randomElement(
            [
                User::USER_VERIFIED,
                User::USER_NOT_VERIFIED,
            ]
        ),
        'verified_token' => $verified === User::USER_VERIFIED ? null : User::generateVerifiedToken(),
        'admin' => $faker->randomElement(
            [
                User::USER_ADMIN,
                User::USER_REGULAR,
            ]
        ),
    ];
});
