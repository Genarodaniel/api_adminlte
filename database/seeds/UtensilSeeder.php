<?php

use Illuminate\Database\Seeder;
use App\Http\Models\Utensil;

class UtensilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Utensil::class, 10)->create();
    }
}
