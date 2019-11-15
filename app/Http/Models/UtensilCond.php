<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UtensilCond extends Model
{
    protected $table = "utensilscond";
    protected $fillable = [
        'condominium_id', 'utensil_id'
    ];
}
