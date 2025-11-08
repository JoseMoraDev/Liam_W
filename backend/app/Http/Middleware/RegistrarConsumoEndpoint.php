<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\UbicacionEndpointUsuario;
use App\Models\Endpoint;

class RegistrarConsumoEndpoint
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Solo registramos si el usuario está autenticado y no es una petición preflight
        if ($request->isMethod('OPTIONS')) {
            return $response;
        }

        $user = Auth::user();
        if (!$user) {
            return $response;
        }

        try {
            $route = $request->route();
            if (!$route) {
                return $response;
            }
            $uri = method_exists($route, 'uri') ? $route->uri() : ($route->uri ?? null);
            if (!$uri || !is_string($uri)) {
                return $response;
            }
            $ruta = '/' . ltrim($uri, '/');

            $endpoint = Endpoint::where('url', $ruta)->first();
            if (!$endpoint) {
                // Crear endpoint si no existe para poder contabilizar usos
                $endpoint = Endpoint::create([
                    'name' => $ruta,
                    'tipo' => 'auth',
                    'url' => $ruta,
                ]);
            }

            if ($endpoint) {
                $params = $request->all();

                // Detectar tipo_ubicacion según los parámetros
                $tipoUbicacion = 'sin_ubicacion';
                $valores = [
                    'valor_lat' => $params['lat'] ?? null,
                    'valor_lon' => $params['lon'] ?? null,
                    'valor_id_municipio' => $params['municipio'] ?? null,
                    'valor_codigo_playa' => $params['codigo_playa'] ?? null,
                    'valor_codigo_montana' => $params['codigo_montana'] ?? null,
                    'valor_codigo_area' => $params['codigo_area'] ?? null,
                    'valor_codigo_zona' => $params['codigo_zona'] ?? null,
                ];

                if ($valores['valor_lat'] && $valores['valor_lon']) {
                    $tipoUbicacion = 'coordenadas';
                } elseif ($valores['valor_id_municipio']) {
                    $tipoUbicacion = 'municipio';
                } elseif ($valores['valor_codigo_playa']) {
                    $tipoUbicacion = 'codigo_playa';
                } elseif ($valores['valor_codigo_montana']) {
                    $tipoUbicacion = 'codigo_montana';
                }

                // Buscar si ya existe registro para este usuario + endpoint + parámetros
                $registro = UbicacionEndpointUsuario::where('user_id', $user->id)
                    ->where('endpoint_id', $endpoint->id)
                    ->where('tipo_ubicacion', $tipoUbicacion)
                    ->where(function ($q) use ($valores) {
                        foreach ($valores as $campo => $valor) {
                            $q->where($campo, $valor);
                        }
                    })->first();

                if ($registro) {
                    $registro->increment('usos');
                } else {
                    UbicacionEndpointUsuario::create(array_merge([
                        'user_id' => $user->id,
                        'endpoint_id' => $endpoint->id,
                        'tipo_ubicacion' => $tipoUbicacion,
                        'usos' => 1,
                        'predeterminada' => 0,
                    ], $valores));
                }
            }
        } catch (\Throwable $e) {
            // No romper el flujo de la API por métricas
            return $response;
        }

        return $response;
    }
}

// voy en chatgpt en 
// Vale, vamos a analizarlo con cuidado: