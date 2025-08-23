<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Endpoint;
use App\Models\UbicacionEndpointUsuario;

class UbicacionesTestSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario existente
        $user = User::find(1); // nett@nett.es

        if (!$user) {
            $this->command->error("Usuario id=1 no encontrado");
            return;
        }

        // Endpoint de prueba
        $endpoint = Endpoint::firstOrCreate([
            'nombre' => 'Predicción Playa',
            'url' => '/api/aemet/playa',
            'tipo' => 'aemet'
        ]);

        // Ubicación inicial para este usuario y endpoint
        UbicacionEndpointUsuario::firstOrCreate([
            'user_id' => $user->id,
            'endpoint_id' => $endpoint->id,
            'tipo_ubicacion' => 'municipio',
            'valor_id_municipio' => '28079', // Madrid
        ], [
            'usos' => 0,
            'predeterminada' => 0,
            'nombre_amigable' => 'Madrid'
        ]);
    }
}
