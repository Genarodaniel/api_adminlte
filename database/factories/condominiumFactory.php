<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use App\Http\Models\Condominium;
use Faker\Generator as Faker;

$factory->define(Condominium::class, function (Faker $faker) {
    
    $users_app = App\Http\Models\User_app::pluck('id')->toArray();
    return [
        'address_street' => $faker->streetName,
		'address_number' => $faker->randomNumber(4),
		'address_city' => $faker->city,
		'address_complement' => $faker->SecondaryAddress,
        'address_state' => $faker->state,
        'address_state_abbr'=>$faker->stateAbbr,
        'address_country' => $faker->country,
        'manager_id'=>$faker->randomElement($users_app),
    ];
});
