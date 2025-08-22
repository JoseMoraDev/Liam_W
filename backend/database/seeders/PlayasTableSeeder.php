<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlayasTableSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('seeders/playas.txt');

        if (!file_exists($filePath)) {
            $this->command->error("No se encontró el archivo: $filePath");
            return;
        }

        $file = fopen($filePath, 'r');
        $firstLine = true;

        while (($line = fgetcsv($file, 0, ';')) !== false) {
            if ($firstLine) {
                $firstLine = false;
                continue; // saltar cabecera
            }

            [
                $id_playa,
                $nombre_playa,
                $id_provincia,
                $nombre_provincia,
                $id_municipio,
                $nombre_municipio,
                $lat_dms,
                $lon_dms
            ] = $line;

            DB::table('playas')->insert([
                'id_playa'         => $id_playa,
                'nombre_playa'     => $nombre_playa,
                'id_provincia'     => $id_provincia,
                'nombre_provincia' => $nombre_provincia,
                'id_municipio'     => $id_municipio,
                'nombre_municipio' => $nombre_municipio,
                'lat_dms'          => $lat_dms,
                'lon_dms'          => $lon_dms,
                'lat'              => $this->dmsToDecimal($lat_dms),
                'lon'              => $this->dmsToDecimal($lon_dms),
            ]);
        }

        fclose($file);
    }

    private function dmsToDecimal($dms)
    {
        // Elimina símbolos y normaliza espacios
        $dmsClean = str_replace(['°', "'", '"'], [' ', ' ', ' '], trim($dms));
        $parts = preg_split('/\s+/', $dmsClean);

        if (count($parts) < 3) {
            return null;
        }

        $deg = floatval($parts[0]);
        $min = floatval($parts[1]);
        $sec = floatval($parts[2]);

        $decimal = $deg + ($min / 60) + ($sec / 3600);

        // Si es negativo o hay indicaciones de Sur/Oeste
        if (strpos($dms, '-') !== false || stripos($dms, 'S') !== false || stripos($dms, 'W') !== false) {
            $decimal *= -1;
        }

        return round($decimal, 7);
    }
}
