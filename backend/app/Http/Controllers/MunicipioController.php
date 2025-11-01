<?php

namespace App\Http\Controllers;

use App\Models\Municipio;

class MunicipioController extends Controller
{
    public function getByProvincia($cpro)
    {
        return Municipio::where('cpro', $cpro)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }
}
