<?php

namespace App\Http\Controllers;

use App\Models\Municipio;

class MunicipioController extends Controller
{
    public function getByProvincia($cpro)
    {
        $items = Municipio::where('cpro', $cpro)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json(
            $items,
            200,
            ['Content-Type' => 'application/json; charset=UTF-8'],
            JSON_UNESCAPED_UNICODE
        );
    }
}
