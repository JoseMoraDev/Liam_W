<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiCallsDefinitionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('api_calls_definitions')->insert([
            [
                'name' => 'TomTom Traffic Flow',
                'token_shortcode' => 'tom',
                'base_url' => 'https://api.tomtom.com/traffic',
                'path' => '/flowSegmentData',
                'method' => 'GET',
                'path_placeholders' => json_encode([]),
                'query_placeholders' => json_encode(['key', 'point']),
                'auth_type' => 'query',
                'default_format' => 'json',
                'notes' => 'API para tráfico TomTom',
            ],
            [
                'name' => 'WAQI Air Quality',
                'token_shortcode' => 'aqi',
                'base_url' => 'https://api.waqi.info',
                'path' => '/feed',
                'method' => 'GET',
                'path_placeholders' => json_encode(['city']),
                'query_placeholders' => json_encode(['token']),
                'auth_type' => 'query',
                'default_format' => 'json',
                'notes' => 'API para calidad del aire AQI',
            ],
            [
                'name' => 'AEMET Weather',
                'token_shortcode' => 'aem',
                'base_url' => 'https://opendata.aemet.es',
                'path' => '/opendata/api',
                'method' => 'GET',
                'path_placeholders' => json_encode([]),
                'query_placeholders' => json_encode(['api_key']),
                'auth_type' => 'header',
                'default_format' => 'json',
                'notes' => 'API meteorológica AEMET',
            ],
        ]);
    }
}
