<?php

use Illuminate\Database\Seeder;

class Users_app_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\User_app::class, 40)->create();
    }
}
