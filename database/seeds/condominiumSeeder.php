<?php

use Illuminate\Database\Seeder;

class condominiumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Condominium::class,40)->create();
    }
}
