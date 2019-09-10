<?php

use Illuminate\Database\Seeder;
use App\Http\Models\Condominium;

class condominiumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Condominium::class,40)->create();
    }
}
