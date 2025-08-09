<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiCallsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('api_calls')->insert([
            [
                'description' => 'Call to TomTom API traffic data',
                'params' => json_encode(['key' => 'tu_api_key', 'point' => '40.4168,-3.7038']),
                'url' => 'https://api.tomtom.com/traffic/flowSegmentData',
                'token_id' => 1,  // id del token de TomTom en tu tabla tokens
                'user_id' => 1,   // id del usuario que hace la llamada
                'definition_id' => 1,  // id del api_calls_definitions para TomTom
            ],
            [
                'description' => 'Call to AQI API for Madrid',
                'params' => json_encode(['city' => 'Madrid', 'token' => 'tu_token']),
                'url' => 'https://api.waqi.info/feed/Madrid',
                'token_id' => 2,
                'user_id' => 1,
                'definition_id' => 2,
            ],
            [
                'description' => 'Call to AEMET API for Madrid',
                'params' => json_encode(['id' => '28079', 'api_key' => 'tu_api_key']),
                'url' => 'https://opendata.aemet.es/opendata/api/prediccion/especifica/municipio/28079',
                'token_id' => 3,
                'user_id' => 1,
                'definition_id' => 3,
            ],
        ]);
    }
}
