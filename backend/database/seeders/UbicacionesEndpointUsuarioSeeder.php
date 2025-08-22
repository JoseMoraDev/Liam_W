<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbicacionesEndpointUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ubicaciones_endpoint_usuario')->insert([
            [
                'user_id' => 1,
                'endpoint_id' => 1,
                'tipo_ubicacion' => 'coordenadas',
                'valor_lat' => 40.416775,
                'valor_lon' => -3.70379,
                'valor_id_municipio' => null,
                'nombre_amigable' => 'Madrid Centro',
                'usos' => 0,
                'predeterminada' => 1,
            ],
            [
                'user_id' => 1,
                'endpoint_id' => 5,
                'tipo_ubicacion' => 'municipio',
                'valor_lat' => null,
                'valor_lon' => null,
                'valor_id_municipio' => '28079',
                'nombre_amigable' => 'Madrid',
                'usos' => 0,
                'predeterminada' => 0,
            ],
        ]);
    }
}
