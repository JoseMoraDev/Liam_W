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

// Rutas de comprobación (solo local)
Route::middleware('local-only')->prefix('check')->group(function () {
  Route::get('/health', [HealthController::class, 'checkAPI']);
  Route::get('/db', [HealthController::class, 'checkDB']);
});

// Todas las rutas que requieren usuario autenticado y registran consumo
Route::middleware(['auth:sanctum', 'registrar-consumo'])->group(function () {
  // Ubicaciones
  Route::post('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'storeOrUpdate']);
  Route::get('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'index']);
  Route::post('/ubicaciones/{id}/predeterminada', [UbicacionEndpointUsuarioController::class, 'setPredeterminada']);

  // Predicción por municipio
  Route::get('/prediccion/diaria/{municipioId}', [AemetController::class, 'prediccionDiariaMunicipio']);
  Route::get('/prediccion/horaria/{municipioId}', [AemetController::class, 'prediccionHorariaMunicipio']);

  // Tráfico TomTom
  Route::get('/tomtom/traffic-flow', [TomTomController::class, 'trafficFlow']);
  Route::get('/tomtom/traffic-incidents', [TomTomController::class, 'trafficIncidents']);

  // Calidad del aire AQICN
  Route::get('/aqicn/feed-here', [AQICNController::class, 'feedHere']);
  Route::get('/aqicn/feed-geo', [AQICNController::class, 'feedGeo']);

  // Información local
  Route::get('playas', [PlayaController::class, 'index']);
  Route::get('playa/{id}', [PlayaController::class, 'show']);
  Route::get('/comunidades-provincias', [ComunidadesProvinciasController::class, 'index']);
  Route::get('municipios/{provincia}', [AemetController::class, 'getMunicipiosByProvincia']);

  // AEMET
  Route::get('/aemet/nivologica/{area_nivologica}', [AemetController::class, 'prediccionNivologica']);
  Route::get('aemet/montana/{area_montana}/{dia_montana}', [AemetController::class, 'prediccionMontana']);
  Route::get('/aemet/playa/{id_playa}', [AemetController::class, 'prediccionPlaya']);
  Route::get('/aemet/sst', [AemetController::class, 'temperaturaSuperficieMar']);

  // Alertas CAP
  Route::get('/avisos_cap/{tipo}/{codigo?}', [AemetCapController::class, 'mostrarAviso']);
  Route::get('/aemet/avisos_cap/meteoalerta/{region}', [AemetController::class, 'avisosCapMeteoalerta']);
  Route::get('/aemet/avisos_cap/avisos_cap_es', [AemetController::class, 'avisosCapEspaña']);
  Route::get('/aemet/avisos_cap/ultimoelaborado/area/{area}/{format?}', [AemetController::class, 'avisosCapUltimoElaborado']);
});
