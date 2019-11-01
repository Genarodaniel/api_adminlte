<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DayToDay extends Model
{
    protected $table = "DayToDay";
    protected $fillable = [
        'day', 'name'
    ];
}
