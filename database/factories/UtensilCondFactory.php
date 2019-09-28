<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use Faker\Generator as Faker;
use App\Http\Models\Utensil;
use App\Http\Models\UtensilCond;
use App\Http\Models\Condominium;

$factory->define(UtensilCond::class, function (Faker $faker) {
    $condominium = Condominium::pluck('id')->toArray();
    $utensil = Utensil::pluck('id')->toArray();

    return [
        'condominium_id' => $faker->randomElement($condominium),
		'utensil_id' => $faker->randomElement($utensil)
    ];
});
