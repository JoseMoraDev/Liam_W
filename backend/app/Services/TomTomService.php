<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TomTomService
{
    public function getTrafficData($lat, $lon)
    {
        // Obtener token desde DB
        $token = DB::table('tokens')
            ->where('shortcode', 'tom')
            ->value('token');

        if (!$token) {
            throw new \Exception("Token para TomTom no encontrado en la base de datos.");
        }

        // Endpoint de TomTom (ejemplo)
        $url = "https://api.tomtom.com/traffic/services/4/flowSegmentData/relative0/json";

        // Llamada HTTP
        $response = Http::get($url, [
            'point' => "{$lat},{$lon}",
            'key'   => $token
        ]);

        if ($response->failed()) {
            throw new \Exception("Error al llamar a TomTom: " . $response->body());
        }

        return $response->json();
    }
}
