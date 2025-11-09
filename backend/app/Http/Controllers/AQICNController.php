<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        try {
            $response = Http::retry(2, 800)
                ->timeout(12)
                ->connectTimeout(8)
                ->get($url, [ 'token' => $token ]);

            if ($response->failed()) {
                Log::warning('[AQICN][feed-here] failed', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json([
                    'status' => 'error',
                    'error' => 'Error en llamada AQICN feed/here',
                    'details' => mb_substr($response->body(), 0, 400)
                ], 502, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }

            $json = $response->json();
            return response()->json($json, 200, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            Log::error('[AQICN][feed-here] exception', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'error' => 'Excepción al llamar AQICN', 'message' => $e->getMessage()], 502);
        }
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
        try {
            $response = Http::retry(2, 800)
                ->timeout(12)
                ->connectTimeout(8)
                ->get($url, [ 'token' => $token ]);

            if ($response->failed()) {
                Log::warning('[AQICN][feed-geo] failed', ['status' => $response->status(), 'body' => $response->body(), 'lat' => $lat, 'lon' => $lon]);
                return response()->json([
                    'status' => 'error',
                    'error' => 'Error en llamada AQICN feed/geo',
                    'details' => mb_substr($response->body(), 0, 400)
                ], 502, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }

            $json = $response->json();
            return response()->json($json, 200, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            Log::error('[AQICN][feed-geo] exception', ['error' => $e->getMessage(), 'lat' => $lat, 'lon' => $lon]);
            return response()->json(['status' => 'error', 'error' => 'Excepción al llamar AQICN', 'message' => $e->getMessage()], 502);
        }
    }
}
