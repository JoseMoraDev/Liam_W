<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Database\Seeders\FontsTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\ColorsTableSeeder;
use Database\Seeders\LocationsTableSeeder;
use Database\Seeders\TokensTableSeeder;
use Database\Seeders\ApiCallsDefinitionsTableSeeder;
use Database\Seeders\WeatherDataTableSeeder;
use Database\Seeders\ApiCallsTableSeeder;
use Database\Seeders\ComunidadesProvinciasSeeder;
use Database\Seeders\MunicipiosTableSeeder;
use Database\Seeders\PlayasTableSeeder;
use Database\Seeders\MontanosasTableSeeder;
use Database\Seeders\ComunidadesProvinciasAreasSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FontsTableSeeder::class,
            UsersTableSeeder::class,
            ColorsTableSeeder::class,
            LocationsTableSeeder::class,
            TokensTableSeeder::class,
            ApiCallsDefinitionsTableSeeder::class,
            WeatherDataTableSeeder::class,
            ApiCallsTableSeeder::class,
            ComunidadesProvinciasSeeder::class,
            MontanosasTableSeeder::class,
            ComunidadesProvinciasAreasSeeder::class,
            MunicipiosTableSeeder::class,
            PlayasTableSeeder::class,
            EndpointsTableSeeder::class,
        ]);
    }
}
