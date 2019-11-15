<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DayToDay extends Model
{
    protected $table = "daytoday";
    protected $fillable = [
        'day', 'name'
    ];
}
