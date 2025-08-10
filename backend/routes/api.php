<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
// Api controllers
use App\Http\Controllers\TomTomController;
use App\Http\Controllers\AQICNController;

Route::middleware('local-only')->prefix('check')->group(function () {
  Route::get('/health', [HealthController::class, 'checkAPI']);
  Route::get('/db', [HealthController::class, 'checkDB']);
});

Route::get('/tomtom/traffic-flow', [TomTomController::class, 'trafficFlow']);
Route::get('/tomtom/traffic-incidents', [TomTomController::class, 'trafficIncidents']);
Route::get('/aqicn/feed-here', [AQICNController::class, 'feedHere']);
Route::get('/aqicn/feed-geo', [AQICNController::class, 'feedGeo']);
