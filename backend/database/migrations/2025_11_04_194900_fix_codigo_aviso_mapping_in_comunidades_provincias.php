<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Reset codigo_aviso according to authoritative mapping; others -> 'esp'
        $map = [
            '01' => '61', // Andalucía
            '02' => '62', // Aragón
            '03' => '63', // Asturias, Principado de
            '04' => '64', // Illes Balears
            '05' => '65', // Canarias
            '06' => '66', // Cantabria
            '07' => '67', // Castilla y León
            '08' => '68', // Castilla - La Mancha
            '09' => '69', // Cataluña
            '10' => '70', // Extremadura
            '11' => '71', // Galicia
            '12' => '72', // Madrid, Comunidad de
            '13' => '73', // Murcia, Región de
            '14' => '74', // Navarra, Comunidad Foral de
            '15' => '75', // País Vasco
            '16' => '76', // Rioja, La
            '17' => '77', // Comunitat Valenciana
            '18' => '78', // Ceuta
            '19' => '79', // Melilla
        ];

        // Set default 'esp' for all
        DB::table('comunidades_provincias')->update(['codigo_aviso' => 'esp']);

        // Apply precise mapping by codauto
        foreach ($map as $codauto => $codigo) {
            DB::table('comunidades_provincias')
                ->where('codauto', $codauto)
                ->update(['codigo_aviso' => $codigo]);
        }
    }

    public function down(): void
    {
        // No-op rollback: leave values as-is
    }
};
