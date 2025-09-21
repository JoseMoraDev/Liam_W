<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('login', [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
        $middleware->validateCsrfTokens(
            except: ['api'],
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
