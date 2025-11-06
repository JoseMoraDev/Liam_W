<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $rows = DB::table('comunidades_provincias')->select('id','nomauto','codigo_aviso')->get();

        $map = [
            'espana' => 'esp',
            'andalucia' => '61',
            'aragon' => '62',
            'asturiasprincipadode' => '63',
            'illesbalears' => '64',
            'islasbaleares' => '64',
            'baleares' => '64',
            'ceuta' => '78',
            'canarias' => '65',
            'cantabria' => '66',
            'castillayleon' => '67',
            'castillalamancha' => '68',
            'cataluna' => '69',
            'catalunya' => '69',
            'extremadura' => '70',
            'galicia' => '71',
            'madridcomunidadde' => '72',
            'melilla' => '79',
            'murciaregionde' => '73',
            'navarracomunidadforalde' => '74',
            'paisvasco' => '75',
            'larioja' => '76',
            'comunitatvalenciana' => '77',
            'comunidadvalenciana' => '77',
            'valencia' => '77',
        ];

        foreach ($rows as $r) {
            $key = self::norm($r->nomauto ?? '');
            $codigo = $map[$key] ?? 'esp';
            DB::table('comunidades_provincias')->where('id', $r->id)->update(['codigo_aviso' => $codigo]);
        }
    }

    public function down(): void
    {
        // no rollback
    }

    private static function norm(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $s);
        $s = preg_replace('/[^a-z0-9]+/','', $s);
        return $s;
    }
};
