<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API requests should not use encrypted cookies or session
        $middleware->statefulApi();
        
        // Register CORS middleware for API routes
        $middleware->web(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON response for unauthenticated API requests
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Please provide a valid Bearer token.',
                    'error' => 'authentication_required'
                ], 401);
            }
        });
    })->create();
