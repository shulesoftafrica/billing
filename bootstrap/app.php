<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Http\Middleware\HandleCors;
use App\Http\Middleware\AppAccessTokenMiddleware;
use App\Http\Middleware\MultiAuthMiddleware;
use App\Http\Middleware\EnsureOrganizationScope;
use App\Exceptions\StripePaymentException;

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

        $middleware->alias([
            'app.access.token' => AppAccessTokenMiddleware::class,
            'auth.multi' => MultiAuthMiddleware::class,
            'organization.scope' => EnsureOrganizationScope::class,
            'api.logger' => \App\Http\Middleware\ApiRequestLogger::class,
        ]);
        
        // Register CORS middleware for API routes
        $middleware->web(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON responses for all API routes - no HTML redirects
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || 
                   $request->expectsJson() || 
                   str_contains($request->path(), 'api/');
        });

        // Return JSON response for unauthenticated API requests - MUST be before validation
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson() || str_contains($request->path(), 'api/')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'authentication_required',
                    'hint' => 'Please include Authorization header with Bearer token'
                ], 401);
            }
        });

        // Return JSON response for validation errors
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson() || str_contains($request->path(), 'api/')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (StripePaymentException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => $e->toArray(),
                ], $e->httpStatus());
            }
        });
    })->create();
