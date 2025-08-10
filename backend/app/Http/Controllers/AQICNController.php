<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AQICNController extends Controller
{
    protected function getToken()
    {
        $tokenRecord = DB::table('tokens')->where('shortcode', 'aqi')->first();
        return $tokenRecord ? $tokenRecord->token : null;
    }

    // Obtener calidad del aire según IP del cliente (feed/here)
    public function feedHere(Request $request)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AQICN no encontrado'], 500);
        }

        $url = 'https://api.waqi.info/feed/here/';

        $response = Http::get($url, [
            'token' => $token,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error en llamada AQICN feed/here', 'details' => $response->body()], 500);
        }

        return response()->json($response->json());
    }

    // Obtener calidad del aire según coordenadas GPS (feed/geo:lat;lon)
    public function feedGeo(Request $request)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AQICN no encontrado'], 500);
        }

        $lat = $request->input('lat');
        $lon = $request->input('lon');

        if (!$lat || !$lon) {
            return response()->json(['error' => 'Faltan parámetros lat y lon'], 400);
        }

        $url = "https://api.waqi.info/feed/geo:$lat;$lon/";

        $response = Http::get($url, [
            'token' => $token,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error en llamada AQICN feed/geo', 'details' => $response->body()], 500);
        }

        return response()->json($response->json());
    }
}
