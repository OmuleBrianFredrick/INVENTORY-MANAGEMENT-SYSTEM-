<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: []);

        // Feature tests submit POST requests directly without a browser-issued
        // CSRF token. Keep CSRF protection enabled for real requests, while
        // allowing the testing environment to exercise controller behaviour.
        // Read the PHPUnit environment directly here because the application
        // container is still being bootstrapped when this callback executes.
        if (($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null) === 'testing') {
            $middleware->validateCsrfTokens(except: ['*']);
        }

        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
