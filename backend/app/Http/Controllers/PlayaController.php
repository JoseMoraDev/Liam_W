<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayaController extends Controller
{
    // Devuelve todas las playas
    public function index()
    {
        $playas = DB::table('playas')->get();
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
