<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;
use App\Http\Models\Utensil;
use App\Http\Models\Condominium;

$factory->define(Utensil::class, function (Faker $faker) {
    $condominium = Condominium::pluck('id')->toArray();
    return [
        'name' => 'churrasqueira',
		'description' => 'com picanha'
    ];
});
