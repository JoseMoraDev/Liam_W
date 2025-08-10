<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPlayasFromCsv extends Command
{
    protected $signature = 'import:playas {file=playas.csv}';
    protected $description = 'Importa playas desde un CSV (ID_PLAYA;NOMBRE_PLAYA;...;LATITUD;LONGITUD) y convierte DMS a decimal';

    public function handle()
    {
        $fileArg = $this->argument('file');
        $filePath = base_path($fileArg);
        if (!file_exists($filePath)) {
            // probar storage/app
            $filePath = storage_path('app/' . $fileArg);
            if (!file_exists($filePath)) {
                $this->error("Fichero no encontrado: {$fileArg}. Pon el CSV en la raíz del proyecto o en storage/app/");
                return 1;
            }
        }

        $this->info("Importando CSV desde: {$filePath}");

        // abrir fichero
        if (($handle = fopen($filePath, 'r')) === false) {
            $this->error('No se pudo abrir el fichero');
            return 1;
        }

        // helper para convertir DMS a decimal
        $dmsToDecimal = function ($dms) {
            if ($dms === null) return null;
            $s = trim($dms);
            if ($s === '') return null;

            // Convertir posible encoding
            $s = mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');

            // Normalizar caracteres comunes de grados/min/seg
            $s = str_replace(['º', '′', '’', '″', '″', '“', '”', 'ʻ', 'ʼ'], ["°", "'", "'", "\"", "\"", "\"", "\"", "'", "'"], $s);
            // Reemplazar todos los caracteres no-numéricos (salvo . y -) por espacios
            $clean = preg_replace('/[^\d\.\-]+/', ' ', $s);
            $parts = array_values(array_filter(explode(' ', trim($clean)), fn($p) => $p !== ''));

            // Si hay sólo un número lo tomamos como decimal
            if (count($parts) === 1) {
                return floatval($parts[0]);
            }

            // Si menos de 3 elementos, rellena con ceros
            while (count($parts) < 3) {
                $parts[] = '0';
            }

            // Mantener signo si existe en el primer componente
            $sign = 1;
            if (strpos($parts[0], '-') === 0) {
                $sign = -1;
                $parts[0] = ltrim($parts[0], '-');
            }

            $deg = floatval($parts[0]);
            $min = floatval($parts[1]);
            $sec = floatval($parts[2]);

            $decimal = $deg + ($min / 60) + ($sec / 3600);
            return $sign * $decimal;
        };

        // Leer cabecera
        $header = fgetcsv($handle, 0, ';', '"');
        if ($header === false) {
            $this->error('CSV vacío o con formato incorrecto.');
            fclose($handle);
            return 1;
        }

        // Normalizar nombres de columnas (mayúsculas, trim)
        $header = array_map(function ($h) {
            return trim(strtoupper($h));
        }, $header);

        // Quitar BOM UTF-8 si existe en la primera columna
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        $this->info('Cabecera: ' . implode('|', $header));

        $rowCount = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
                if (count($row) <= 1) continue;

                $row = array_map('trim', $row);

                // Asegurar que el número de columnas coincide o rellenar con nulls
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), null);
                }

                $data = @array_combine($header, $row);
                if (!$data) {
                    $this->error('Error combinando fila con cabecera.');
                    continue;
                }

                $this->info('Fila leída: ' . implode('|', $row));
                $this->info('Array combinado keys: ' . implode('|', array_keys($data)));

                $id_playa = $data['ID_PLAYA'] ?? null;
                if (empty($id_playa)) {
                    $this->warn('Fila saltada por id_playa vacío: ' . implode('|', $row));
                    continue;
                }

                $nombre_playa = $data['NOMBRE_PLAYA'] ?? null;
                $id_provincia = $data['ID_PROVINCIA'] ?? null;
                $nombre_provincia = $data['NOMBRE_PROVINCIA'] ?? null;
                $id_municipio = $data['ID_MUNICIPIO'] ?? null;
                $nombre_municipio = $data['NOMBRE_MUNICIPIO'] ?? null;
                $lat_dms = $data['LATITUD'] ?? null;
                $lon_dms = $data['LONGITUD'] ?? null;

                // Convertir a decimal
                $lat_decimal = $dmsToDecimal($lat_dms);
                $lon_decimal = $dmsToDecimal($lon_dms);

                // Insertar o actualizar
                DB::table('playas')->updateOrInsert(
                    ['id_playa' => $id_playa],
                    [
                        'nombre_playa' => $nombre_playa,
                        'id_provincia' => $id_provincia,
                        'nombre_provincia' => $nombre_provincia,
                        'id_municipio' => $id_municipio,
                        'nombre_municipio' => $nombre_municipio,
                        'lat_dms' => $lat_dms,
                        'lon_dms' => $lon_dms,
                        'lat' => $lat_decimal,
                        'lon' => $lon_decimal,
                    ]
                );

                $rowCount++;
            }

            DB::commit();
            fclose($handle);
            $this->info("Importadas/actualizadas {$rowCount} playas.");
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
