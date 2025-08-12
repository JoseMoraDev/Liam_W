<?php

namespace App\Http\Controllers\Api;

use App\Models\ComunidadProvincia;
use App\Http\Controllers\Controller;

class ComunidadesProvinciasController extends Controller
{
    public function index()
    {
        // Agrupamos las provincias por comunidad
        $datos = ComunidadProvincia::all()
            ->groupBy('nomauto')
            ->map(function ($provincias, $comunidad) {
                return [
                    'codauto' => $provincias->first()->codauto,
                    'nomauto' => $comunidad,
                    'provincias' => $provincias->map(function ($prov) {
                        return [
                            'cpro' => $prov->cpro,
                            'nompro' => $prov->nompro,
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json($datos);
    }
}
