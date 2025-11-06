<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $rows = DB::table('comunidades_provincias')->select('id','nomauto')->get();

        foreach ($rows as $r) {
            $n = $this->norm($r->nomauto ?? '');
            $codigo = null;
            if (str_contains($n, 'asturias')) {
                $codigo = '63';
            } elseif ($n === 'comunidaddemadrid' || str_contains($n, 'madrid')) {
                $codigo = '72';
            } elseif ($n === 'regiondemurcia' || str_contains($n, 'murcia')) {
                $codigo = '73';
            } elseif (str_contains($n, 'navarra')) {
                $codigo = '74';
            }
            if ($codigo) {
                DB::table('comunidades_provincias')->where('id', $r->id)->update(['codigo_aviso' => $codigo]);
            }
        }
    }

    public function down(): void
    {
        // no rollback
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $trans = iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $s);
        if ($trans !== false) { $s = $trans; }
        $s = preg_replace('/[^a-z0-9]+/', '', $s);
        return $s;
    }
};
