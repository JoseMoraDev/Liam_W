<?php

namespace App\Http\Middleware;

use App\Models\Endpoint;
use App\Models\EndpointHit;
use App\Models\UbicacionEndpointUsuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ContarHitsPublicos
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        try {
            if ($request->isMethod('OPTIONS')) {
                return;
            }
            $path = ltrim($request->path(), '/');
            $excludePrefixes = [
                'telescope',
                'horizon',
                'nova',
                'storage',
                'vendor',
                'debugbar',
                'favicon.ico',
                'sanctum/csrf-cookie',
                'api/admin/metrics',
                'api/admin/users',
                'api/login',
                'api/register',
                'api/forgot-password',
                'api/reset-password',
                'api/logout',
                'api/me',
            ];
            if (str_starts_with($path, 'api/admin/metrics') || $path === 'sanctum/csrf-cookie') {
                return;
            }
            foreach ($excludePrefixes as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    return;
                }
            }

            $route = $request->route();
            $uri = null;
            if ($route) {
                $uri = method_exists($route, 'uri') ? $route->uri() : ($route->uri ?? null);
            }
            $uri = $uri ?: $path;
            $ruta = '/' . ltrim($uri, '/');

            $endpoint = Endpoint::firstOrCreate(
                ['url' => $ruta],
                ['name' => $ruta, 'tipo' => 'public', 'url' => $ruta]
            );

            EndpointHit::create([
                'endpoint_id' => $endpoint->id,
                'path' => '/' . $path,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->header('User-Agent'),
            ]);

            try {
                $user = Auth::user();
                // If not authenticated via guard (public route), try resolve via Sanctum token header
                if (!$user) {
                    $authHeader = (string) $request->header('Authorization');
                    if (str_starts_with($authHeader, 'Bearer ')) {
                        $plainToken = substr($authHeader, 7);
                        if ($plainToken) {
                            // Try standard Sanctum resolution (supports id|token format)
                            $pat = PersonalAccessToken::findToken($plainToken);
                            if ($pat && $pat->tokenable_type === \App\Models\User::class) {
                                $user = $pat->tokenable; // User model
                            } else {
                                // Fallback: manual SHA-256 hash lookup when only the plain part is sent
                                try {
                                    $parts = explode('|', $plainToken, 2);
                                    $justPlain = count($parts) === 2 ? ($parts[1] ?? '') : $plainToken;
                                    if ($justPlain !== '') {
                                        $hash = hash('sha256', $justPlain);
                                        $rec = \Illuminate\Support\Facades\DB::table('personal_access_tokens')->where('token', $hash)->first();
                                        if ($rec && isset($rec->tokenable_type, $rec->tokenable_id) && $rec->tokenable_type === \App\Models\User::class) {
                                            $user = \App\Models\User::find($rec->tokenable_id);
                                        }
                                    }
                                } catch (\Throwable $e) { /* ignore */ }
                            }
                        }
                    }
                }
                if ($user) {
                    $rec = UbicacionEndpointUsuario::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'endpoint_id' => $endpoint->id,
                            'tipo_ubicacion' => 'sin_ubicacion',
                        ],
                        [
                            'usos' => 0,
                        ]
                    );
                    $rec->increment('usos');
                }
            } catch (\Throwable $e) {
                // ignore
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
