<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\UserLocationPref;
use App\Models\Municipio;
use Illuminate\Support\Facades\DB;

class UserLocationPrefController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            $uid = $request->query('user_id');
            if ($uid) {
                $user = User::find($uid);
            }
        }
        if ($user) {
            $pref = UserLocationPref::firstOrCreate(['user_id' => $user->id]);
        } else {
            // Modo relajado: devolver la última preferencia guardada globalmente
            $pref = UserLocationPref::orderByDesc('updated_at')->first();
            if (!$pref) {
                // Sin datos
                return response()->json([
                    'ccaa_id' => null,
                    'cpro' => null,
                    'area_code' => null,
                    'municipio_id' => null,
                    'municipio_name' => null,
                    'lat' => null,
                    'lon' => null,
                    'avisos_region' => 'esp',
                ]);
            }
        }
        // Normalizar municipio_id de salida a formato concatenado cpro+cmun (e.g., 03065)
        $munOut = $pref->municipio_id;
        try {
            if (!empty($pref->municipio_id)) {
                $mid = (string)$pref->municipio_id;
                if (ctype_digit($mid) && strlen($mid) <= 4) {
                    $row = DB::table('municipios')->where('id', (int)$mid)->first();
                    if ($row) {
                        $cpro = str_pad((string)$row->cpro, 2, '0', STR_PAD_LEFT);
                        $cmun = str_pad((string)$row->cmun, 3, '0', STR_PAD_LEFT);
                        $munOut = $cpro . $cmun;
                        if (empty($pref->cpro)) { $pref->cpro = $cpro; }
                        if (empty($pref->municipio_name) && isset($row->nombre)) { $pref->municipio_name = $row->nombre; }
                    }
                }
            }
        } catch (\Throwable $e) { /* ignore */ }

        return response()->json([
            'ccaa_id' => $pref->ccaa_id,
            'cpro' => $pref->cpro,
            'area_code' => $pref->area_code,
            'municipio_id' => $munOut,
            'municipio_name' => $pref->municipio_name,
            'lat' => $pref->lat,
            'lon' => $pref->lon,
            'avisos_region' => $pref->avisos_region,
            'codigo_playa' => $pref->codigo_playa ?? null,
        ]);
    }

    public function store(Request $request)
    {
        // Permitir guardar sin sesión si se proporciona user_id válido
        $user = $request->user();
        if (!$user) {
            $uid = $request->input('user_id');
            if ($uid) {
                $user = User::find($uid);
            }
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
        }
        $data = $request->validate([
            'ccaa_id' => ['nullable','string','max:255'],
            'cpro' => ['nullable','string','max:10'],
            'area_code' => ['nullable','string','max:255'],
            'municipio_id' => ['nullable','string','max:255'],
            'municipio_name' => ['nullable','string','max:255'],
            'codigo_playa' => ['nullable','string','max:50'],
            'user_id' => ['nullable','integer'],
        ]);
        // Normalizar municipio_id entrante a formato concatenado si viene como PK
        if (!empty($data['municipio_id'])) {
            $mid = (string)$data['municipio_id'];
            if (ctype_digit($mid) && strlen($mid) <= 4) {
                try {
                    $row = DB::table('municipios')->where('id', (int)$mid)->first();
                    if ($row) {
                        $cpro = str_pad((string)$row->cpro, 2, '0', STR_PAD_LEFT);
                        $cmun = str_pad((string)$row->cmun, 3, '0', STR_PAD_LEFT);
                        $data['municipio_id'] = $cpro . $cmun;
                        $data['cpro'] = $data['cpro'] ?? $cpro;
                        $data['municipio_name'] = $data['municipio_name'] ?? ($row->nombre ?? null);
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }
        }

        $pref = UserLocationPref::updateOrCreate(
            ['user_id' => $user->id],
            [
                'ccaa_id' => $data['ccaa_id'] ?? null,
                'cpro' => $data['cpro'] ?? null,
                'area_code' => $data['area_code'] ?? null,
                'municipio_id' => $data['municipio_id'] ?? null,
                'municipio_name' => $data['municipio_name'] ?? null,
                'codigo_playa' => $data['codigo_playa'] ?? ($pref->codigo_playa ?? null),
            ]
        );

        // Resolver coordenadas del municipio (cachear en DB)
        $lat = null; $lon = null;
        if (!empty($pref->municipio_id)) {
            $mun = null;
            try {
                $mid = (string)$pref->municipio_id;
                if (ctype_digit($mid) && strlen($mid) <= 4) {
                    $mun = DB::table('municipios')->where('id', (int)$mid)->first();
                } elseif (strlen($mid) === 5 && ctype_digit($mid)) {
                    $cpro = substr($mid, 0, 2);
                    $cmun = substr($mid, 2, 3);
                    $mun = DB::table('municipios')->where('cpro', $cpro)->where('cmun', $cmun)->first();
                }
            } catch (\Throwable $e) { $mun = null; }
            if ($mun) {
                $lat = $mun->lat; $lon = $mun->lon;
                // Auto-corregir provincia y metadatos desde municipio/comunidades_provincias
                try {
                    if (!empty($mun->cpro)) {
                        $pref->cpro = str_pad((string)$mun->cpro, 2, '0', STR_PAD_LEFT);
                        $row = DB::table('comunidades_provincias')
                            ->whereIn('cpro', [$mun->cpro, $pref->cpro])
                            ->first();
                        if ($row) {
                            if (isset($row->codauto)) {
                                $pref->ccaa_id = $row->codauto; // codauto como ccaa_id
                            }
                            if (isset($row->codAreaMont)) {
                                $pref->area_code = $row->codAreaMont; // área montañosa
                            }
                            if (isset($row->codigo_aviso) && $row->codigo_aviso) {
                                $pref->avisos_region = $row->codigo_aviso; // código aviso definitivo
                            }
                        }
                    }
                } catch (\Throwable $e) { /* ignore */ }
                if ($lat === null || $lon === null) {
                    // Intentar geocodificar con Nominatim (OpenStreetMap)
                    try {
                        $query = trim(($mun->nombre ?? '') . ', Spain');
                        $resp = Http::timeout(6)->withHeaders([
                            'User-Agent' => 'LiveAmbience/1.0 (contact: dev@example.com)'
                        ])->get('https://nominatim.openstreetmap.org/search', [
                            'q' => $query,
                            'format' => 'json',
                            'limit' => 1,
                        ]);
                        if ($resp->ok() && !empty($resp[0])) {
                            $lat = isset($resp[0]['lat']) ? (float)$resp[0]['lat'] : null;
                            $lon = isset($resp[0]['lon']) ? (float)$resp[0]['lon'] : null;
                            if ($lat !== null && $lon !== null) {
                                $mun->lat = $lat; $mun->lon = $lon; $mun->save();
                            }
                        }
                    } catch (\Throwable $e) {
                        // Silenciar geocoding
                    }
                }
            }
        }

        // Usar codigo_aviso desde comunidades_provincias cuando exista; fallback a mapping por codauto
        $avisos = null;
        try {
            if ($pref->cpro) {
                $cpro = (string)$pref->cpro;
                $cproPadded = str_pad($cpro, 2, '0', STR_PAD_LEFT);
                $row = DB::table('comunidades_provincias')
                    ->whereIn('cpro', [$cpro, $cproPadded])
                    ->first();
                if ($row && isset($row->codigo_aviso) && $row->codigo_aviso) {
                    $avisos = $row->codigo_aviso;
                } elseif ($row && isset($row->codauto)) {
                    $avisos = self::mapAvisosRegion($row->codauto);
                }
            }
        } catch (\Throwable $e) { /* ignore */ }
        if (!$avisos) {
            $avisos = self::mapAvisosRegion($pref->ccaa_id);
        }

        // Guardar extras en la preferencia
        $pref->lat = $lat; $pref->lon = $lon; $pref->avisos_region = $avisos;
        $pref->save();

        return response()->json([
            'success' => true,
            'ccaa_id' => $pref->ccaa_id,
            'cpro' => $pref->cpro,
            'area_code' => $pref->area_code,
            'municipio_id' => $pref->municipio_id,
            'municipio_name' => $pref->municipio_name,
            'lat' => $pref->lat,
            'lon' => $pref->lon,
            'avisos_region' => $pref->avisos_region,
            'codigo_playa' => $pref->codigo_playa ?? null,
        ]);
    }

    private static function mapAvisosRegion($codauto)
    {
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
        $key = is_null($codauto) ? null : str_pad((string)$codauto, 2, '0', STR_PAD_LEFT);
        return $map[$key] ?? 'esp';
    }
}
