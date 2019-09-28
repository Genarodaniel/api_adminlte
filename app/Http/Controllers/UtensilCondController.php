<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UtensilCondController extends Controller
{
    public function __construct(UtensilCond $utensilCond)
    {
        $this->utensilCond = $utensilCond;
    }
}
