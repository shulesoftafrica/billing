<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // For API routes, don't redirect unauthenticated users
        $middleware->redirectGuestsTo(function ($request) {
            return $request->expectsJson() ? null : '/login';
        });
        
        // Disable sessions for API routes (we use tokens)
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions for API requests
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error' => 'Authentication required'
                ], 401);
            }
            
            // For web requests, redirect to login (but we don't have one)
            return redirect()->guest('/login');
        });
    })->create();
