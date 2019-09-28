<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Utensil;

class UtensilController extends Controller
{
    public $sucessStatus = 200;
    private $utensil;

    public function __construct(Utensil $utensil)
    {
        $this->utensil = $utensil;
    }
}
