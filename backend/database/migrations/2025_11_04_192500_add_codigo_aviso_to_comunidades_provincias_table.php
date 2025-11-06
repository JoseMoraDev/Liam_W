<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comunidades_provincias', function (Blueprint $table) {
            if (!Schema::hasColumn('comunidades_provincias', 'codigo_aviso')) {
                $table->string('codigo_aviso', 5)->nullable()->after('codauto');
            }
        });

        // Mapear codauto (01..19) -> codigo_aviso (61..79). Resto a 'esp'
        $map = [
            '01' => '61', // Andalucía
            '02' => '62', // Aragón
            '03' => '63', // Asturias
            '04' => '64', // Illes Balears
            '05' => '65', // Canarias
            '06' => '66', // Cantabria
            '07' => '67', // Castilla y León
            '08' => '68', // Castilla-La Mancha
            '09' => '69', // Cataluña
            '10' => '70', // Extremadura
            '11' => '71', // Galicia
            '12' => '72', // Madrid
            '13' => '73', // Murcia
            '14' => '74', // Navarra
            '15' => '75', // País Vasco
            '16' => '76', // La Rioja
            '17' => '77', // Comunitat Valenciana
            '18' => '78', // Ceuta
            '19' => '79', // Melilla
        ];

        foreach ($map as $codauto => $codigo) {
            DB::table('comunidades_provincias')
                ->where('codauto', $codauto)
                ->update(['codigo_aviso' => $codigo]);
        }

        // Cualquier fila que siga null, poner 'esp'
        DB::table('comunidades_provincias')
            ->whereNull('codigo_aviso')
            ->update(['codigo_aviso' => 'esp']);
    }

    public function down(): void
    {
        Schema::table('comunidades_provincias', function (Blueprint $table) {
            if (Schema::hasColumn('comunidades_provincias', 'codigo_aviso')) {
                $table->dropColumn('codigo_aviso');
            }
        });
    }
};
