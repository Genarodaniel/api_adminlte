<?php

use Illuminate\Database\Seeder;
use App\Http\Models\User_app;

class Users_app_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User_app::class, 10)->create();
    }
}
