<?php

namespace App\Http\Controllers;

use App\Models\ComunidadProvincia;
use Illuminate\Http\Request;

class ComunidadesProvinciasController extends Controller
{
    public function index()
    {
        return ComunidadProvincia::all();
    }
}
