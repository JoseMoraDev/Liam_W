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
            ['name' => 'Tráfico - Flujo', 'url' => '/api/tomtom/traffic-flow', 'tipo' => 'tomtom'],
            ['name' => 'Tráfico - Incidencias', 'url' => '/api/tomtom/traffic-incidents', 'tipo' => 'tomtom'],

            // AQICN
            ['name' => 'Calidad aire - Aquí', 'url' => '/api/aqicn/feed-here', 'tipo' => 'aqicn'],
            ['name' => 'Calidad aire - Coordenadas', 'url' => '/api/aqicn/feed-geo', 'tipo' => 'aqicn'],

            // AEMET
            ['name' => 'Predicción nivológica', 'url' => '/api/aemet/nivologica', 'tipo' => 'aemet'],
            ['name' => 'Predicción montaña', 'url' => '/api/aemet/montana', 'tipo' => 'aemet'],
            ['name' => 'Predicción playa', 'url' => '/api/aemet/playa', 'tipo' => 'aemet'],
            ['name' => 'Temperatura mar (SST)', 'url' => '/api/aemet/sst', 'tipo' => 'aemet'],
            ['name' => 'Avisos CAP', 'url' => '/api/aemet/avisos_cap', 'tipo' => 'aemet'],
            ['name' => 'Predicción diaria por municipio', 'url' => '/api/prediccion/diaria', 'tipo' => 'aemet'],
            ['name' => 'Predicción horaria por municipio', 'url' => '/api/prediccion/horaria', 'tipo' => 'aemet'],
        ]);
    }
}
