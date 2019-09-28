<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Utensil extends Model
{
    protected $table = "utensils";
    protected $fillable = [
        'name', 'description'
    ];
}
