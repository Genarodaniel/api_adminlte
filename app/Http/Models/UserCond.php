<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UserCond extends Model
{
    protected $table = "UsersCond";
    protected $fillable = [
        'condominium_id', 'user_id'
    ];
}
