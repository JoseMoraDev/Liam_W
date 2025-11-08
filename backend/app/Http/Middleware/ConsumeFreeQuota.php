<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;

class ConsumeFreeQuota
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if (!$user) {
            return $response;
        }
        if (($user->role ?? 'free') !== 'free') {
            return $response;
        }
        if ($user->is_blocked ?? false) {
            return $response;
        }

        // Solo contar si la respuesta es 2xx
        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) {
            $today = now()->toDateString();
            if (empty($user->free_daily_date) || $user->free_daily_date->toDateString() !== $today) {
                $user->free_daily_used = 0;
                $user->free_daily_date = $today;
            }
            $user->free_daily_used = (int)($user->free_daily_used ?? 0) + 1;
            $user->save();
        }

        return $response;
    }
}
