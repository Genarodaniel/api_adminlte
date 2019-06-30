<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Condominium extends Model
{
    protected $table = "condominiums";
    protected $fillable = [
        'address_street', 'address_number', 'address_state',
        'address_city','manager_id','address_complement',
        'address_country','address_state_abbr',
    ];
}
