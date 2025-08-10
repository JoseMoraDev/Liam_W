<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AemetController extends Controller
{
    protected function getToken()
    {
        $tokenRecord = DB::table('tokens')->where('shortcode', 'aem')->first();
        return $tokenRecord ? $tokenRecord->token : null;
    }

    /**
     * Método genérico para llamar a cualquier endpoint de AEMET
     * que siga el sistema de doble llamada.
     */
    protected function fetchAemetData($endpoint)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';

        // 1ª llamada para obtener las URLs
        $initialResponse = Http::get($base_url . $endpoint, [
            'api_key' => $token,
        ]);

        if ($initialResponse->failed()) {
            return response()->json([
                'error' => 'Error en llamada inicial AEMET',
                'details' => $initialResponse->body()
            ], 500);
        }

        $body = $initialResponse->json();

        if (!isset($body['datos'])) {
            return response()->json(['error' => 'Respuesta inesperada de AEMET: falta "datos"'], 500);
        }

        // 2ª llamada para obtener los datos reales
        $dataResponse = Http::get($body['datos']);

        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener datos reales de AEMET',
                'details' => $dataResponse->body()
            ], 500);
        }

        // AEMET a veces devuelve JSON y otras veces texto
        $decoded = json_decode($dataResponse->body(), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return response()->json($decoded);
        } else {
            return response($dataResponse->body(), 200)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    /**
     * Predicción nivológica 
     */
    public function prediccionNivologica(Request $request, $area_nivologica)
    {
        if (!in_array($area_nivologica, ['0', '1'])) {
            return response()->json(['error' => 'Parámetro area_nivologica inválido. Solo 0 o 1'], 400);
        }

        $endpoint = "/prediccion/especifica/nivologica/{$area_nivologica}";

        $response = $this->fetchAemetData($endpoint);

        if ($response->status() !== 200) {
            return $response;
        }

        $contenido = $response->getContent();
        // Conversión de ISO-8859-1 a UTF-8 si es texto plano
        $contentType = $response->headers->get('Content-Type');
        if (str_contains($contentType, 'text/plain')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
        } else {
            $contenido = json_decode($contenido, true);
        }

        $zona = $area_nivologica === '0' ? 'Pirineo Catalán' : 'Pirineo Navarro y Aragonés';

        return response()->json([
            'zona' => $zona,
            'boletin' => $contenido,
        ]);
    }

    /**
     * Predicción específica para playas
     */
    public function prediccionPlaya($id_playa)
    {
        $endpoint = "/prediccion/especifica/playa/{$id_playa}";

        $response = $this->fetchAemetData($endpoint);

        // Devuelve directamente la respuesta tal cual venga (JSON o texto)
        return $response;
    }

    /**
     * Predicción específica para montaña
     */
    public function prediccionMontana($area_montana, $dia_montana)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        // Lista de áreas válidas
        $areasValidas = [
            'peu1' => 'Picos de Europa',
            'nav1' => 'Pirineo Navarro',
            'arn1' => 'Pirineo Aragonés',
            'cat1' => 'Pirineo Catalán',
            'rio1' => 'Ibérica Riojana',
            'arn2' => 'Ibérica Aragonesa',
            'mad2' => 'Sierras de Guadarrama y Somosierra',
            'gre1' => 'Sierra de Gredos',
            'nev1' => 'Sierra Nevada',
        ];

        if (!array_key_exists($area_montana, $areasValidas)) {
            return response()->json(['error' => 'Parámetro area_montana inválido'], 400);
        }

        if (!in_array($dia_montana, ['0', '1', '2', '3'])) {
            return response()->json(['error' => 'Parámetro dia_montana inválido'], 400);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';
        $endpoint = "/prediccion/especifica/montaña/pasada/area/{$area_montana}/dia/{$dia_montana}";

        // 1ª llamada
        $response = Http::get($base_url . $endpoint, [
            'api_key' => $token,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error en llamada inicial AEMET', 'details' => $response->body()], 500);
        }

        $body = $response->json();

        if (!isset($body['datos'])) {
            return response()->json(['error' => 'Respuesta inesperada de AEMET, falta "datos"'], 500);
        }

        // 2ª llamada
        $dataResponse = Http::get($body['datos']);

        if ($dataResponse->failed()) {
            return response()->json(['error' => 'Error al obtener datos reales de AEMET', 'details' => $dataResponse->body()], 500);
        }

        // Convertir de ISO-8859-1 a UTF-8
        $texto = mb_convert_encoding($dataResponse->body(), 'UTF-8', 'ISO-8859-1');

        // Intentar decodificar JSON (puede que venga como texto plano)
        $decoded = json_decode($texto, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $boletin = $decoded;
        } else {
            // Si no es JSON válido, devolver como texto tal cual
            $boletin = $texto;
        }

        return response()->json([
            'zona' => $areasValidas[$area_montana],
            'dia' => $dia_montana,
            'boletin' => $boletin
        ]);
    }

    /**
     * Predicción temperatura nivel del mar en .png
     */
    public function temperaturaSuperficieMar()
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';
        $endpoint = '/satelites/producto/sst';

        // 1ª llamada
        $response = Http::get($base_url . $endpoint, [
            'api_key' => $token,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Error en llamada inicial AEMET',
                'details' => $response->body()
            ], 500);
        }

        $body = $response->json();

        if (!isset($body['datos'])) {
            return response()->json([
                'error' => 'Respuesta inesperada de AEMET, falta "datos"'
            ], 500);
        }

        $imageUrl = $body['datos'];

        // 2ª llamada para obtener el PNG real
        $imageResponse = Http::get($imageUrl);
        if ($imageResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener imagen SST',
                'details' => $imageResponse->body()
            ], 500);
        }

        // Convertir a base64 opcionalmente
        $base64 = 'data:image/png;base64,' . base64_encode($imageResponse->body());

        return response()->json([
            'url' => $imageUrl,   // Enlace directo (recomendado para <img src="">)
            'base64' => $base64,  // Opción inline (evita caducidad del enlace)
        ]);
    }
}
