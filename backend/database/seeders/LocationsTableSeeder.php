<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('locations')->insert([
            [
                'name' => 'Madrid',
                'lat_lon' => '40.4168,-3.7038',
                'is_default' => true,
                'user_id' => 1,
            ],
            [
                'name' => 'Elche, Alicante',
                'lat_lon' => '38.2699,-0.7129',
                'is_default' => false,
                'user_id' => 1,
            ],
        ]);
    }
}
