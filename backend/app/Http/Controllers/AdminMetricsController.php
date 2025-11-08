<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UbicacionEndpointUsuario;
use App\Models\Endpoint;
use Illuminate\Support\Facades\File;

class AdminMetricsController extends Controller
{
    protected function ensureAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Forbidden');
        }
    }

    // For each user, return the endpoint they use the most (one row per user)
    public function userTopEndpoint(Request $request)
    {
        $this->ensureAdmin();

        // Pull ALL users to ensure even users without usage appear
        $allUsers = DB::table('users')->select('id','email','name')->get()->keyBy('id');

        // Aggregate hits per user/endpoint
        $agg = UbicacionEndpointUsuario::query()
            ->select(['user_id', 'endpoint_id', DB::raw('SUM(usos) as hits')])
            ->groupBy('user_id', 'endpoint_id')
            ->get();

        // Load endpoint URLs
        $endpointIdsAll = $agg->pluck('endpoint_id')->unique()->all();
        $endpoints = DB::table('endpoints')->whereIn('id', $endpointIdsAll)->select('id','url','name')->get()->keyBy('id');

        // Exclusion config for business-only pick (never count these)
        $exclude = ['/api/me','/sanctum/csrf-cookie'];
        $excludePrefixes = ['/api/admin/'];

        // Group by user
        $grouped = [];
        foreach ($agg as $row) { $grouped[(int)$row->user_id][] = $row; }

        $picked = [];
        foreach ($allUsers as $uid => $userRow) {
            $rows = $grouped[$uid] ?? [];
            $bestExcl = null; $bestAny = null;
            foreach ($rows as $r) {
                $e = $endpoints->get($r->endpoint_id); $url = $e->url ?? ($e->name ?? null);
                if (!$url) continue;
                if ($bestAny === null || $r->hits > $bestAny->hits) { $bestAny = $r; }
                $isInternal = in_array($url, $exclude, true);
                if (!$isInternal) {
                    foreach ($excludePrefixes as $p) { if (str_starts_with($url, $p)) { $isInternal = true; break; } }
                }
                if ($isInternal) continue;
                if ($bestExcl === null || $r->hits > $bestExcl->hits) { $bestExcl = $r; }
            }
            // If user has any usage but only on excluded endpoints (like /api/me or admin), do NOT fallback
            if ($bestExcl) {
                $chosen = $bestExcl;
                $picked[] = [
                    'user_id' => (int)$uid,
                    'endpoint_id' => (int)$chosen->endpoint_id,
                    'hits' => (int)$chosen->hits,
                ];
            } else {
                // No usage for this user yet
                $picked[] = [
                    'user_id' => (int)$uid,
                    'endpoint_id' => null,
                    'hits' => 0,
                ];
            }
        }

        $endpointIds = array_values(array_unique(array_filter(array_map(fn($r) => $r['endpoint_id'], $picked), fn($v) => !is_null($v))));
        $endpointsNeeded = DB::table('endpoints')->whereIn('id', $endpointIds)->select('id','url','name')->get()->keyBy('id');

        $result = array_map(function($r) use ($allUsers, $endpointsNeeded) {
            $u = $allUsers->get($r['user_id']); $e = $r['endpoint_id'] ? $endpointsNeeded->get($r['endpoint_id']) : null;
            return [
                'user_id' => $r['user_id'],
                'email' => $u->email ?? null,
                'name' => $u->name ?? null,
                'endpoint_id' => $r['endpoint_id'],
                'endpoint' => $e ? ($e->url ?? ($e->name ?? null)) : null,
                'hits' => $r['hits'],
            ];
        }, $picked);

        // Sort by hits desc (users with no usage go at the end)
        usort($result, function($a,$b){ return ($b['hits'] <=> $a['hits']) ?: (($a['name'] ?? '') <=> ($b['name'] ?? '')); });
        return response()->json(['top' => $result]);
    }

    // Top users by endpoint (authenticated usage only)
    public function userEndpointsTop(Request $request)
    {
        $this->ensureAdmin();
        $limit = (int) ($request->query('limit', 10));
        if ($limit <= 0 || $limit > 100) { $limit = 10; }

        $rows = UbicacionEndpointUsuario::query()
            ->select([
                'ubicaciones_endpoint_usuario.user_id as user_id',
                'ubicaciones_endpoint_usuario.endpoint_id as endpoint_id',
                DB::raw('SUM(ubicaciones_endpoint_usuario.usos) as hits')
            ])
            ->groupBy('ubicaciones_endpoint_usuario.user_id', 'ubicaciones_endpoint_usuario.endpoint_id')
            ->get();

        // Load endpoints to filter internal ones
        $endpointIdsAll = $rows->pluck('endpoint_id')->unique()->all();
        $endpointsMap = DB::table('endpoints')->whereIn('id', $endpointIdsAll)->select('id','url','name')->get()->keyBy('id');

        // Exclusion rules
        $exclude = ['/api/me','/sanctum/csrf-cookie'];
        $excludePrefixes = ['/api/admin/'];

        // Filter out internal endpoints
        $rows = $rows->filter(function($r) use ($endpointsMap, $exclude, $excludePrefixes) {
            $e = $endpointsMap->get($r->endpoint_id);
            $url = $e->url ?? ($e->name ?? null);
            if (!$url) return false;
            if (in_array($url, $exclude, true)) return false;
            foreach ($excludePrefixes as $p) { if (str_starts_with($url, $p)) return false; }
            return true;
        })
        ->sortByDesc('hits')
        ->take($limit)
        ->values();

        $userIds = $rows->pluck('user_id')->unique()->all();
        $endpointIds = $rows->pluck('endpoint_id')->unique()->all();

        $users = DB::table('users')->whereIn('id', $userIds)->select('id','email','name')->get()->keyBy('id');
        $endpoints = $endpointsMap->only($endpointIds);

        $result = $rows->map(function($r) use ($users, $endpoints) {
            $u = $users->get($r->user_id);
            $e = $endpoints->get($r->endpoint_id);
            return [
                'user_id' => (int) $r->user_id,
                'email' => $u->email ?? null,
                'name' => $u->name ?? null,
                'endpoint_id' => (int) $r->endpoint_id,
                'endpoint' => $e->url ?? ($e->name ?? null),
                'hits' => (int) $r->hits,
            ];
        });

        return response()->json([ 'top' => $result ]);
    }

    // Top endpoints by hits and percentage of total
    public function endpointsTop(Request $request)
    {
        $this->ensureAdmin();

        $limit = (int) ($request->query('limit', 10));
        if ($limit <= 0 || $limit > 100) { $limit = 10; }

        // Authenticated usage (by user & location)
        $authHits = UbicacionEndpointUsuario::select('endpoint_id', DB::raw('SUM(usos) as hits'))
            ->groupBy('endpoint_id')
            ->pluck('hits', 'endpoint_id');

        // Public aggregated hits: count rows per endpoint (one row per hit)
        $publicHits = DB::table('endpoint_hits')->select('endpoint_id', DB::raw('COUNT(*) as hits'))
            ->groupBy('endpoint_id')
            ->pluck('hits', 'endpoint_id');

        // Merge both maps
        $allEndpointIds = $authHits->keys()->merge($publicHits->keys())->unique();
        $totalsByEndpoint = [];
        foreach ($allEndpointIds as $eid) {
            $totalsByEndpoint[$eid] = (int) ($authHits[$eid] ?? 0) + (int) ($publicHits[$eid] ?? 0);
        }

        // Compute global total
        $total = array_sum($totalsByEndpoint);

        // Load endpoint URLs
        $endpoints = DB::table('endpoints')->whereIn('id', array_keys($totalsByEndpoint))->pluck('url', 'id');

        // Exclude internal endpoints from Top (e.g., /api/me)
        foreach ($totalsByEndpoint as $eid => $_hits) {
            $url = $endpoints[$eid] ?? null;
            if ($url === '/api/me') {
                unset($totalsByEndpoint[$eid]);
                unset($endpoints[$eid]);
            }
        }

        // Build rows and sort desc by hits
        $rows = collect($totalsByEndpoint)
            ->map(function ($hits, $endpointId) use ($endpoints, $total) {
                $url = $endpoints[$endpointId] ?? null;
                return [
                    'endpoint_id' => (int) $endpointId,
                    'url' => $url,
                    'hits' => (int) $hits,
                    'percent' => $total > 0 ? round(($hits / $total) * 100, 2) : 0.0,
                ];
            })
            ->sortByDesc('hits')
            ->values()
            ->take($limit);

        return response()->json([
            'total_hits' => (int) $total,
            'top' => $rows,
        ]);
    }

    // Daily usage by role (free) and free quota stats
    public function stats(Request $request)
    {
        $this->ensureAdmin();

        $today = now()->toDateString();

        // Public calls today from endpoint_hits
        $publicToday = DB::table('endpoint_hits')
            ->whereDate('created_at', $today)
            ->count();

        // Authenticated free usage today from users.free_daily_used
        $freeUsersQ = User::query();
        $freeCalls = (int) $freeUsersQ->sum('free_daily_used');
        $activeFreeUsers = (int) $freeUsersQ->where('free_daily_used', '>', 0)->count();
        $maxFree = (int) $freeUsersQ->max('free_daily_used');
        $freeHitQuota = (int) User::whereColumn('free_daily_used', '>=', DB::raw('COALESCE(free_daily_quota, 50)'))->count();

        // Totals across all calls (public + authenticated tracked via free_daily_used)
        $totalCalls = $publicToday + $freeCalls;
        $totalUsers = $activeFreeUsers; // best available proxy for distinct callers today
        $totalAvg = $totalUsers > 0 ? round($totalCalls / $totalUsers, 2) : 0.0;
        $totalMax = max($maxFree, 0);

        return response()->json([
            'date' => $today,
            'usage_by_role' => [
                'total' => [
                    'calls' => $totalCalls,
                    'users' => $totalUsers,
                    'avg' => $totalAvg,
                    'max' => $totalMax,
                ],
                // Keep legacy key for UI compatibility if referenced
                'free' => [
                    'calls' => $freeCalls,
                    'users' => $activeFreeUsers,
                    'avg' => $activeFreeUsers > 0 ? round($freeCalls / $activeFreeUsers, 2) : 0.0,
                    'max' => $maxFree,
                ],
            ],
            'free_quota' => [
                'users_reaching_quota' => $freeHitQuota,
                'public_calls_today' => (int) $publicToday,
            ],
        ]);
    }

    // Login history: last N login attempts (success/failure)
    public function logins(Request $request)
    {
        $this->ensureAdmin();
        $limit = (int)($request->query('limit', 50));
        if ($limit <= 0) { $limit = 50; }
        if ($limit > 5000) { $limit = 5000; }
        $onlySuccessRaw = $request->query('success', null);
        $onlySuccess = is_null($onlySuccessRaw) ? null : filter_var($onlySuccessRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $order = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $q = DB::table('login_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.email', 'l.ip', 'l.user_id', 'l.success', 'l.created_at', 'u.name as user_name', 'u.username')
            ->orderBy('l.created_at', $order)
            ->limit($limit);
        if (!is_null($onlySuccess)) {
            $q->where('success', $onlySuccess);
        }
        $rows = $q->get()->map(function($r){
            return [
                'email' => $r->email,
                'ip' => $r->ip,
                'user_id' => $r->user_id,
                'success' => (bool)$r->success,
                'time' => (string) $r->created_at,
                'user_name' => $r->user_name ?: $r->username,
            ];
        });
        return response()->json($rows);
    }

    // Error logs: tail last N lines of storage/logs/laravel.log
    public function errors(Request $request)
    {
        $this->ensureAdmin();
        $limit = (int)($request->query('limit', 200));
        if ($limit <= 0 || $limit > 1000) { $limit = 200; }

        $path = storage_path('logs/laravel.log');
        if (!File::exists($path)) {
            return response()->json([]);
        }
        $lines = self::tailFile($path, $limit);
        return response()->json($lines);
    }

    protected static function tailFile(string $filepath, int $lines = 200): array
    {
        $f = @fopen($filepath, 'rb');
        if ($f === false) return [];
        $buffer = '';
        $chunkSize = 4096;
        $pos = -1;
        $lineCount = 0;
        $fileSize = filesize($filepath);
        while ($lineCount < $lines && -$pos < $fileSize) {
            $step = min($chunkSize, $fileSize + $pos + 1);
            $pos -= $step;
            fseek($f, $pos, SEEK_END);
            $buffer = fread($f, $step) . $buffer;
            $lineCount = substr_count($buffer, "\n");
        }
        fclose($f);
        $arr = explode("\n", trim($buffer));
        // Return last N lines
        return array_slice($arr, -$lines);
    }
}
