<?php

use App\Http\Models\UtensilCond;
use Illuminate\Database\Seeder;

class UtensilCondSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(UtensilCond::class, 10)->create();
    }
}
