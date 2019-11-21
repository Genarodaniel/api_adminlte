<?php
   namespace App\Http\Models;

   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Foundation\Auth\User as Authenticatable;
   use Laravel\Passport\HasApiTokens;
   use Illuminate\Notifications\Notifiable;
     class User_app extends Authenticatable
     {
        use HasApiTokens, Notifiable;
        protected $fillable = [
            'name', 'email', 'password','user_type'
        ];

        protected $hidden = [
            'password', 'remember_token',
        ];

    }
