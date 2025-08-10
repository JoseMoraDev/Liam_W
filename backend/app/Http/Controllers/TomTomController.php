<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TomTomController extends Controller
{
    protected function getToken()
    {
        $tokenRecord = DB::table('tokens')->where('shortcode', 'tom')->first();
        return $tokenRecord ? $tokenRecord->token : null;
    }

    public function trafficFlow(Request $request)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token TomTom no encontrado'], 500);
        }

        // Parámetros obligatorios
        $point = $request->input('point');
        $unit = $request->input('unit', 'KMPH'); // valor por defecto

        if (!$point) {
            return response()->json(['error' => 'Falta parámetro point'], 400);
        }

        $url = 'https://api.tomtom.com/traffic/services/4/flowSegmentData/absolute/10/json';

        $response = Http::get($url, [
            'point' => $point,
            'unit' => $unit,
            'key' => $token,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error en llamada TomTom traffic flow', 'details' => $response->body()], 500);
        }

        return response()->json($response->json());
    }

    public function trafficIncidents(Request $request)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token TomTom no encontrado'], 500);
        }

        $bbox = $request->input('bbox');
        if (!$bbox) {
            return response()->json(['error' => 'Falta parámetro bbox'], 400);
        }

        $fields = '{incidents{type,geometry{type,coordinates},properties{iconCategory}}}';
        $language = 'en-GB';
        $categoryFilter = '0,1,2,3,4,5,6,7,8,9,10,11,14';
        $timeValidityFilter = 'present';

        $url = 'https://api.tomtom.com/traffic/services/5/incidentDetails';

        $response = Http::get($url, [
            'bbox' => $bbox,
            'fields' => $fields,
            'language' => $language,
            'categoryFilter' => $categoryFilter,
            'timeValidityFilter' => $timeValidityFilter,
            'key' => $token,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error en llamada TomTom incidents', 'details' => $response->body()], 500);
        }

        return response()->json($response->json());
    }
}
