<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public static function checkAPI()
    {
        return response()->json(['message' => 'API works!']);
    }

    public static function checkDB()
    {
        try {
            DB::connection()->getPdo();
            return response()->json(['message' => 'Database connection works!']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'DB connection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
