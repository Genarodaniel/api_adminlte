<?php
   
   namespace App;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Foundation\Auth\User as Authenticatable;

     class User_app extends Authenticatable{
        
        protected $fillable = [
            'name', 'email', 'password','user_type'
        ];

        protected $hidden = [
            'password', 'remember_token',
        ];

        protected $casts = [
            'email_verified_at' => 'datetime',
        ];

    }

    ?>