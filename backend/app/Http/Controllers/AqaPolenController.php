<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AqaPolenController extends Controller
{
    /**
     * Devuelve los datos horarios de polen para Elche (Alicante)
     * Fuente: Open-Meteo Air Quality API
     */
    public function index(Request $request)
    {
        try {
            // 📍 Coordenadas: aceptar query params con fallback a Elche
            $lat = $request->query('lat');
            $lon = $request->query('lon');
            if (!is_numeric($lat) || !is_numeric($lon)) {
                $lat = 38.2699; // Elche
                $lon = -0.7126;
            }

            // 🌿 URL de la API pública de Open-Meteo (polen)
            $url = "https://air-quality-api.open-meteo.com/v1/air-quality?latitude={$lat}&longitude={$lon}&hourly=birch_pollen,grass_pollen,olive_pollen";

            // ⏱️ Petición con timeout de 15 segundos
            $response = Http::timeout(15)->get($url);

            // ❌ Si la respuesta falla
            if ($response->failed()) {
                return response()->json([
                    'error' => 'No se pudo obtener los datos de polen',
                    'details' => $response->body(),
                ], 500);
            }

            // ✅ Decodificamos el JSON
            $data = $response->json();

            // 🕒 Convertimos las horas a formato local (es-ES)
            $labels = array_map(function ($t) {
                return date('D H:i', strtotime($t));
            }, $data['hourly']['time']);

            // 📦 Devolvemos solo la información relevante
            return response()->json([
                'labels' => $labels,
                'abedul' => $data['hourly']['birch_pollen'],
                'gramineas' => $data['hourly']['grass_pollen'],
                'olivo' => $data['hourly']['olive_pollen'],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            // ⚠️ Manejamos errores genéricos
            return response()->json([
                'error' => 'Error inesperado obteniendo datos de polen',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
