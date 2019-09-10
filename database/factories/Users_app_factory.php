<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Http\Models\User_app;
use Faker\Generator as Faker;

$factory->define(User_app::class, function (Faker $faker) {
    static $password;
    return [
        'name' => $faker->name,
		'email' => $faker->unique()->safeEmail,
		'user_type' => 'ar',
		'email_verified_at' => now(),
		'password' => $password ?: $password = bcrypt('secret'),
		'remember_token' => str_random(10),
    ];
});
