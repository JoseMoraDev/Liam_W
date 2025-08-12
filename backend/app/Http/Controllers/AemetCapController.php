<?php

namespace App\Http\Controllers;

use App\Services\AemetCapService;

class AemetCapController extends Controller
{
    protected $aemetCapService;

    public function __construct(AemetCapService $aemetCapService)
    {
        $this->aemetCapService = $aemetCapService;
    }

    public function mostrarAviso($tipo, $codigo = null)
    {
        // Ejemplo de endpoint: "avisos_cap/meteoalerta/cataluna"
        $endpoint = "avisos_cap/{$tipo}";
        if ($codigo) {
            $endpoint .= "/{$codigo}";
        }

        $datos = $this->aemetCapService->obtenerAvisoCap($endpoint);

        return response()->json($datos);
    }
}
