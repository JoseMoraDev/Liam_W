<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EndpointsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('endpoints')->insert([
            // TomTom
            ['nombre' => 'Tráfico - Flujo', 'url' => '/api/tomtom/traffic-flow', 'tipo' => 'tomtom'],
            ['nombre' => 'Tráfico - Incidencias', 'url' => '/api/tomtom/traffic-incidents', 'tipo' => 'tomtom'],

            // AQICN
            ['nombre' => 'Calidad aire - Aquí', 'url' => '/api/aqicn/feed-here', 'tipo' => 'aqicn'],
            ['nombre' => 'Calidad aire - Coordenadas', 'url' => '/api/aqicn/feed-geo', 'tipo' => 'aqicn'],

            // AEMET
            ['nombre' => 'Predicción nivológica', 'url' => '/api/aemet/nivologica', 'tipo' => 'aemet'],
            ['nombre' => 'Predicción montaña', 'url' => '/api/aemet/montana', 'tipo' => 'aemet'],
            ['nombre' => 'Predicción playa', 'url' => '/api/aemet/playa', 'tipo' => 'aemet'],
            ['nombre' => 'Temperatura mar (SST)', 'url' => '/api/aemet/sst', 'tipo' => 'aemet'],
            ['nombre' => 'Avisos CAP', 'url' => '/api/aemet/avisos_cap', 'tipo' => 'aemet'],
            ['nombre' => 'Predicción diaria por municipio', 'url' => '/api/prediccion/diaria', 'tipo' => 'aemet'],
            ['nombre' => 'Predicción horaria por municipio', 'url' => '/api/prediccion/horaria', 'tipo' => 'aemet'],
        ]);
    }
}
