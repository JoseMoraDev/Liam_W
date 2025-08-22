<?php

use Illuminate\Support\Facades\Route;

// Api controllers
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TomTomController;
use App\Http\Controllers\AQICNController;
use App\Http\Controllers\AemetController;
use App\Http\Controllers\PlayaController;
use App\Http\Controllers\AemetCapController;
use App\Http\Controllers\ComunidadesProvinciasController;
use App\Http\Controllers\UbicacionEndpointUsuarioController;

Route::middleware('local-only')->prefix('check')->group(function () {
  Route::get('/health', [HealthController::class, 'checkAPI']);
  Route::get('/db', [HealthController::class, 'checkDB']);
});

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'storeOrUpdate']);
  Route::get('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'index']);
  Route::post('/ubicaciones/{id}/predeterminada', [UbicacionEndpointUsuarioController::class, 'setPredeterminada']);
});

//! Tráfico API TomTom

// flujo de tráfico
Route::get('/tomtom/traffic-flow', [TomTomController::class, 'trafficFlow']);

// accidentes
Route::get('/tomtom/traffic-incidents', [TomTomController::class, 'trafficIncidents']);



//! Calidad del aire API AQICN

// por IP
Route::get('/aqicn/feed-here', [AQICNController::class, 'feedHere']);

// por geolocalización
Route::get('/aqicn/feed-geo', [AQICNController::class, 'feedGeo']);



//! información local guardada en base de datos

// listado de playas
Route::get('playas', [PlayaController::class, 'index']);

// infomación de playa
Route::get('playa/{id}', [PlayaController::class, 'show']);

// Listado de autonomías y provincias
Route::get('/comunidades-provincias', [ComunidadesProvinciasController::class, 'index']);


// Listar municipios por provincia
Route::get('municipios/{provincia}', [AemetController::class, 'getMunicipiosByProvincia']);


//! Condiciones meteorológicas API AEMET

// nivologica
Route::get('/aemet/nivologica/{area_nivologica}', [AemetController::class, 'prediccionNivologica']);

// montaña
Route::get('aemet/montana/{area_montana}/{dia_montana}', [AemetController::class, 'prediccionMontana']);

// playa
Route::get('/aemet/playa/{id_playa}', [AemetController::class, 'prediccionPlaya']);

// temperatura del mar
Route::get('/aemet/sst', [AemetController::class, 'temperaturaSuperficieMar']);

// alertas
Route::get('/avisos_cap/{tipo}/{codigo?}', [AemetCapController::class, 'mostrarAviso']);

//?    Avisos CAP AEMET - borrar?
Route::get('/aemet/avisos_cap/meteoalerta/{region}', [AemetController::class, 'avisosCapMeteoalerta']);

//?    Avisos CAP AEMET - borrar?
Route::get('/aemet/avisos_cap/avisos_cap_es', [AemetController::class, 'avisosCapEspaña']);

// Avisos CAP AEMET
Route::get('/aemet/avisos_cap/ultimoelaborado/area/{area}/{format?}', [AemetController::class, 'avisosCapUltimoElaborado']);

// Predicción diaria por municipio
Route::get('/prediccion/diaria/{municipioId}', [AemetController::class, 'prediccionDiariaMunicipio']);

// Predicción horaria por municipio
Route::get('/prediccion/horaria/{municipioId}', [AemetController::class, 'prediccionHorariaMunicipio']);
