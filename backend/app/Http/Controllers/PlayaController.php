<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayaController extends Controller
{
    // Lista de playas con filtros y proyección de columnas para reducir payload
    public function index(Request $request)
    {
        // Campos permitidos (whitelist)
        $allowed = [
            'id_playa', 'nombre_playa',
            'id_provincia', 'nombre_provincia',
            'id_municipio', 'nombre_municipio',
            'lat', 'lon',
            // bajo demanda:
            'lat_dms', 'lon_dms',
        ];

        // Proyección solicitada (?fields=id_playa,nombre_playa,...)
        $fieldsParam = $request->query('fields');
        $defaultFields = ['id_playa','nombre_playa','id_provincia','nombre_provincia','id_municipio','nombre_municipio','lat','lon'];
        $fields = $defaultFields;
        if ($fieldsParam) {
            $asked = array_filter(array_map('trim', explode(',', $fieldsParam)));
            $fields = array_values(array_intersect($asked, $allowed));
            if (empty($fields)) { $fields = $defaultFields; }
        }

        $q = DB::table('playas')->select($fields);

        // Filtros opcionales
        if ($prov = $request->query('provincia')) {
            $q->where('id_provincia', $prov);
        }
        if ($mun = $request->query('municipio')) {
            $munStr = (string)$mun;
            // Si parece ser la PK de municipios (<= 4 dígitos), intentar resolver a cpro+cmun
            if (strlen($munStr) <= 4 && ctype_digit($munStr)) {
                try {
                    $row = DB::table('municipios')->where('id', (int)$munStr)->first();
                    if ($row && isset($row->cpro) && isset($row->cmun)) {
                        $cpro = str_pad((string)$row->cpro, 2, '0', STR_PAD_LEFT);
                        $cmun = str_pad((string)$row->cmun, 3, '0', STR_PAD_LEFT);
                        $munStr = $cpro . $cmun; // e.g., 03 + 065 => 03065
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }
            $munNoZero = ltrim($munStr, '0');
            $q->where(function($w) use ($munStr, $munNoZero) {
                $w->where('id_municipio', $munStr)
                  ->orWhere('id_municipio', $munNoZero);
            });
        }
        if ($munName = $request->query('municipio_nombre')) {
            $munName = trim($munName);
            if (strpos($munName, '/') !== false) {
                $parts = array_filter(array_map('trim', explode('/', $munName)));
                $q->where(function($w) use ($parts) {
                    foreach ($parts as $p) {
                        $w->orWhere('nombre_municipio', 'like', "%$p%");
                    }
                });
            } else {
                $q->where('nombre_municipio', 'like', $munName);
            }
        }
        if ($exMun = $request->query('exclude_municipio')) {
            $exStr = (string)$exMun;
            // Si parece ser la PK de municipios, resolver a cpro+cmun
            if (strlen($exStr) <= 4 && ctype_digit($exStr)) {
                try {
                    $row = DB::table('municipios')->where('id', (int)$exStr)->first();
                    if ($row && isset($row->cpro) && isset($row->cmun)) {
                        $cpro = str_pad((string)$row->cpro, 2, '0', STR_PAD_LEFT);
                        $cmun = str_pad((string)$row->cmun, 3, '0', STR_PAD_LEFT);
                        $exStr = $cpro . $cmun; // e.g., 03065
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }
            $exNoZero = ltrim($exStr, '0');
            $q->whereNotIn('id_municipio', [$exStr, $exNoZero]);
        }
        if ($search = $request->query('q')) {
            $q->where(function($w) use ($search) {
                $w->where('nombre_playa', 'like', "%$search%")
                  ->orWhere('nombre_municipio', 'like', "%$search%")
                  ->orWhere('nombre_provincia', 'like', "%$search%");
            });
        }

        $q->orderBy('nombre_playa');

        $limit = (int) $request->query('limit', 1000);
        if ($limit <= 0) { $limit = 1000; }
        if ($limit > 5000) { $limit = 5000; }

        $playas = $q->limit($limit)->get();
        return response()->json($playas);
    }

    // Devuelve una playa por id_playa
    public function show($id)
    {
        $playa = DB::table('playas')->where('id_playa', $id)->first();
        if (!$playa) {
            return response()->json(['error' => 'Playa no encontrada'], 404);
        }
        return response()->json($playa);
    }
}
