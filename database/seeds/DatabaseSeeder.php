<?php

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         //$this->call(Users_app_seeder::class);
         //$this->call(condominiumSeeder::class);
         $this->call(UtensilCondSeed::class);
    }
}
