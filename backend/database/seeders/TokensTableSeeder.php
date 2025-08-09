<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TokensTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('tokens')->insert([
            [
                'shortcode' => 'aem',
                'api_desc' => 'Token for AEMET weather API',
                'token' => 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI4NmphbWdAZ21haWwuY29tIiwianRpIjoiMTI0MzhlNTgtMDI3Yy00ZDQ3LTlkMTUtYTRlZDRjMjk5YzVlIiwiaXNzIjoiQUVNRVQiLCJpYXQiOjE3NDMwMTM0MzIsInVzZXJJZCI6IjEyNDM4ZTU4LTAyN2MtNGQ0Ny05ZDE1LWE0ZWQ0YzI5OWM1ZSIsInJvbGUiOiIifQ.QGMwnVMWv1YOGfN7zmK09MqGWfT3pGkWDGxvaYphRug',
                'placeholder_key' => 'api_key',
            ],
            [
                'shortcode' => 'aqi',
                'api_desc' => 'Token for WAQI Air Quality API',
                'token' => 'b3cfd3e3221d2e6e3ecc1c5014a107adf2facbed',
                'placeholder_key' => 'token',
            ],
            [
                'shortcode' => 'tom',
                'api_desc' => 'Token for TomTom Traffic API',
                'token' => '356wlfXGZZ0RmX2rn8MgxGQMg30Wy9q8',
                'placeholder_key' => 'key',
            ],
        ]);
    }
}
