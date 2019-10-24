<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\UtensilCond;

class UserCondController extends Controller
{
    public function __construct(UtensilCond $utensilCond)
    {
        $this->utensilCond = $utensilCond;
    }
    public function exists($condominium_id)
    {
        return $this->utensilCond->where('condominium_id', $condominium_id);
    }
}
