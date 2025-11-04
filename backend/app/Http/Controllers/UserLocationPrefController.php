<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserLocationPref;

class UserLocationPrefController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $pref = UserLocationPref::firstOrCreate(['user_id' => $user->id]);
        return response()->json([
            'ccaa_id' => $pref->ccaa_id,
            'cpro' => $pref->cpro,
            'area_code' => $pref->area_code,
            'municipio_id' => $pref->municipio_id,
            'municipio_name' => $pref->municipio_name,
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
            'user_id' => ['nullable','integer'],
        ]);
        $pref = UserLocationPref::updateOrCreate(
            ['user_id' => $user->id],
            [
                'ccaa_id' => $data['ccaa_id'] ?? null,
                'cpro' => $data['cpro'] ?? null,
                'area_code' => $data['area_code'] ?? null,
                'municipio_id' => $data['municipio_id'] ?? null,
                'municipio_name' => $data['municipio_name'] ?? null,
            ]
        );
        return response()->json([
            'success' => true,
            'ccaa_id' => $pref->ccaa_id,
            'cpro' => $pref->cpro,
            'area_code' => $pref->area_code,
            'municipio_id' => $pref->municipio_id,
            'municipio_name' => $pref->municipio_name,
        ]);
    }
}
