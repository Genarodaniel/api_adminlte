<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UtensilSchedule extends Model
{
    protected $table = "UtensilsSchedule";
    protected $fillable = [
        'days_work', 'utensil_id','work_start','work_end','max_time'
    ];
}
