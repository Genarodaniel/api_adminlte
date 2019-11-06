<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Reserv extends Model
{
    protected $table = "Reserv";
    protected $fillable = [
        'day', 'utensil_id','user_id','time','hour_start','hour_end'
    ];
}
