<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Controladores
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UbicacionEndpointUsuarioController;
use App\Http\Controllers\ComunidadesProvinciasController;
use App\Http\Controllers\AemetController;
use App\Http\Controllers\AemetCapController;
use App\Http\Controllers\PlayaController;
use App\Http\Controllers\TomTomController;
use App\Http\Controllers\AQICNController;
use App\Http\Controllers\AqaPolenController;

// =============================
// 🔐 AUTENTICACIÓN
// =============================

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Perfil del usuario autenticado
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ubicaciones del usuario
    Route::get('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'index']);
    Route::post('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'storeOrUpdate']);
    Route::post('/ubicaciones/{id}/predeterminada', [UbicacionEndpointUsuarioController::class, 'setPredeterminada']);
});

// =============================
// 🚗 TRÁFICO (API TomTom)
// =============================

Route::get('/tomtom/traffic-flow', [TomTomController::class, 'trafficFlow']);
Route::get('/tomtom/traffic-incidents', [TomTomController::class, 'trafficIncidents']);

// =============================
// 🌫️ CALIDAD DEL AIRE (API AQICN)
// =============================

Route::get('/aqicn/feed-here', [AQICNController::class, 'feedHere']);
Route::get('/aqicn/feed-geo', [AQICNController::class, 'feedGeo']);

// =============================
// 🌾 POLEN (API AQA)
// =============================

Route::get('/polen', [AqaPolenController::class, 'index']);

// =============================
// 🏖️ INFORMACIÓN LOCAL (BD)
// =============================

Route::get('/playas', [PlayaController::class, 'index']);
Route::get('/playa/{id}', [PlayaController::class, 'show']);

// Comunidades y provincias
Route::get('/comunidades-provincias', [ComunidadesProvinciasController::class, 'index']);
Route::get('/municipios/{provincia}', [AemetController::class, 'getMunicipiosByProvincia']);

// =============================
// 🌦️ AEMET (Datos meteorológicos)
// =============================

// Predicciones y avisos
Route::get('/aemet/nivologica/{area_nivologica}', [AemetController::class, 'prediccionNivologica']);
Route::get('/aemet/montana/{area_montana}/{dia_montana}', [AemetController::class, 'prediccionMontana']);
Route::get('/aemet/playa/{id_playa}', [AemetController::class, 'prediccionPlaya']);
Route::get('/aemet/sst', [AemetController::class, 'temperaturaSuperficieMar']);
Route::get('/avisos_cap/{tipo}/{codigo?}', [AemetCapController::class, 'mostrarAviso']);
Route::get('/aemet/avisos_cap/ultimoelaborado/area/{area}/{format?}', [AemetController::class, 'avisosCapUltimoElaborado']);
Route::get('/prediccion/diaria/{municipioId}', [AemetController::class, 'prediccionDiariaMunicipio']);
Route::get('/prediccion/horaria/{municipioId}', [AemetController::class, 'prediccionHorariaMunicipio']);
