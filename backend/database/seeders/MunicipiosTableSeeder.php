<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Municipio;

class MunicipiosTableSeeder extends Seeder
{
    public function run(): void
    {
        mb_internal_encoding('UTF-8');  // Establecer codificación interna UTF-8

        Municipio::query()->delete();

        $filePath = database_path('seeders/municipios.txt');

        // Leer el archivo y convertir la codificación a UTF-8
        $rawContent = file_get_contents($filePath);
        $content = mb_convert_encoding($rawContent, 'UTF-8', 'ISO-8859-1');
        $lines = explode("\n", $content);

        array_shift($lines); // quitar cabecera

        $data = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = preg_split('/\s+/', $line, 5);
            if (count($parts) < 5) continue;

            $data[] = [
                'codauto' => $parts[0],
                'cpro' => $parts[1],
                'cmun' => $parts[2],
                'dc' => $parts[3],
                'nombre' => $parts[4],
            ];
        }

        foreach (array_chunk($data, 1000) as $chunk) {
            Municipio::insert($chunk);
        }
    }
}
