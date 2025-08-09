<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeatherDataTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('weather_data')->insert([
            [
                'location_id' => 1,
                'endpoint_id' => 1,
                'data' => json_encode(['temp' => 25, 'humidity' => 60]),
                'fetched_at' => now(),
            ],
        ]);
    }
}
