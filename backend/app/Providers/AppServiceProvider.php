<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-per-user', function ($request) {
            $user = $request->user();
            if (!$user) {
                return Limit::perMinute(0)->by($request->ip());
            }
            if (property_exists($user, 'is_blocked') && $user->is_blocked) {
                return Limit::perMinute(0)->by('blocked:'.$user->id);
            }
            $key = 'user:'.$user->id;
            return match ($user->role ?? 'free') {
                'admin' => Limit::none(),
                'premium' => Limit::none(),
                default => Limit::perMinutes(1440, max(0, (int)($user->free_daily_quota ?? 50)))->by($key),
            };
        });
    }
}
