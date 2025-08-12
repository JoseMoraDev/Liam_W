<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComunidadesProvinciasSeeder extends Seeder
{
    public function run()
    {
        $file = database_path('seeders/comunidades_provincias.txt');

        if (!file_exists($file)) {
            $this->command->error("El archivo {$file} no existe");
            return;
        }


        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->command->error("No se pudo abrir el archivo {$file}");
            return;
        }

        // Saltar cabecera
        // fgetcsv($handle, 1000, ',');

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) < 4) {
                continue; // Saltar líneas mal formateadas
            }
            DB::table('comunidades_provincias')->insert([
                'codauto' => trim($data[0]),
                'nomauto' => trim($data[1]),
                'cpro' => trim($data[2]),
                'nompro' => trim($data[3]),
            ]);
        }

        fclose($handle);
        $this->command->info('Datos insertados correctamente.');
    }
}
