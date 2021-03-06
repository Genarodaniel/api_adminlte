<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\UtensilCond;

class UtensilCondController extends Controller
{
    public function __construct(UtensilCond $utensilCond)
    {
        $this->utensilCond = $utensilCond;
    }

    public function exists($utensil_id)
    {
        return DB::table('utensilsCond')->where('utensil_id','=',$utensil_id)->first();
    }
}
