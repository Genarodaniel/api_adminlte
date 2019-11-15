<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UserCond extends Model
{
    protected $table = "userscond";
    protected $fillable = [
        'condominium_id', 'user_id'
    ];
}
