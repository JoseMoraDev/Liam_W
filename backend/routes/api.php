<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::middleware('local-only')->prefix('check')->group(function () {
  Route::get('/health', [HealthController::class, 'checkAPI']);
  Route::get('/db', [HealthController::class, 'checkDB']);
});
