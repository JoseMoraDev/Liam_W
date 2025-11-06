<?php

// TODO: quitar username de la tabla de usuarios
// TODO: CLASIFICAR TODOS LOS
use App\Http\Controllers\AemetCapController;
use App\Http\Controllers\AemetController;
use App\Http\Controllers\MunicipioController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperarPasswd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;

// Api controllers
use App\Http\Controllers\AQICNController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComunidadesProvinciasController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PlayaController;
use App\Http\Controllers\TomTomController;
use App\Http\Controllers\AqaPolenController;
use App\Http\Controllers\UbicacionEndpointUsuarioController;
use App\Http\Controllers\UserLocationPrefController;

Route::post('/cambiar-passwd', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('token', $request->token)
        ->first();

    if (!$record) {
        return response()->json([
            'success' => false,
            'message' => 'Token inválido o expirado.'
        ], 400);
    }

    // Actualizar contraseña
    DB::table('users')
        ->where('email', $record->email)
        ->update(['password' => Hash::make($request->password)]);

    // Borrar token
    DB::table('password_reset_tokens')->where('email', $record->email)->delete();

    return response()->json([
        'success' => true,
        'message' => 'Contraseña cambiada correctamente.'
    ]);
});


Route::post('/recuperar-passwd', function (Request $request) {
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    // TODO: VALIDAR EL USUARIO ASI
    // if (!$user) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'No existe ningún usuario con ese correo.'
    //     ], 404);
    // }

    $token = Str::random(64);

    // Guardar token en la tabla
    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        ['token' => $token, 'created_at' => now()]
    );

    // Enviar correo
    Mail::to($request->email)->send(new RecuperarPasswd($token));

    return response()->json([
        'success' => true,
        'message' => 'Correo de recuperación enviado correctamente.'
    ]);
});


//! Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


//! middleware (SPA cookie-based): ensure web session + sanctum
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ubicaciones
    Route::post('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'storeOrUpdate']);
    Route::get('/ubicaciones', [UbicacionEndpointUsuarioController::class, 'index']);
    Route::post('/ubicaciones/{id}/predeterminada', [UbicacionEndpointUsuarioController::class, 'setPredeterminada']);

    // Preferencias de ubicación del usuario (última selección) [protegido por sesión]
    Route::get('/user/location-pref', [UserLocationPrefController::class, 'show']);
});

// Ruta pública para guardar preferencia de ubicación (relajada por requerimiento)
Route::post('/user/location-pref', [UserLocationPrefController::class, 'store']);

// Ruta pública para consultar preferencia de ubicación (acepta user_id)
Route::get('/user/location-pref', [UserLocationPrefController::class, 'show']);




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


//! Concentración de Polen API AQA
Route::get('/polen', [AqaPolenController::class, 'index']);


//! información local guardada en base de datos
// listado de playas
Route::get('/playas', [PlayaController::class, 'index']);

// infomación de playa
Route::get('/playa/{id}', [PlayaController::class, 'show']);

// Listado de autonomías y provincias
Route::get('/comunidades-provincias', [ComunidadesProvinciasController::class, 'index']);

// Listar municipios por provincia
Route::get('/municipios/{provincia}', [AemetController::class, 'getMunicipiosByProvincia']);
Route::get('/municipios/{cpro}', [MunicipioController::class, 'getByProvincia']);


//! Condiciones meteorológicas API AEMET
// nivologica
Route::get('/aemet/nivologica/{area_nivologica}', [AemetController::class, 'prediccionNivologica']);

// montaña
Route::get('/aemet/montana/{area_montana}/{dia_montana}', [AemetController::class, 'prediccionMontana']);

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
