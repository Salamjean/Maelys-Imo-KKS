<?php

use App\Http\Middleware\AgenceMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.commercial.status' => \App\Http\Middleware\CheckCommercialStatus::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Redirection login selon le contexte (session expirée / non connecté)
        $loginRedirect = function (Request $request) {
            if ($request->expectsJson()) {
                return null; // laisser le handler JSON par défaut
            }
            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect()->route('admin.login')->withErrors(['error' => 'Votre session a expiré. Veuillez vous reconnecter.']);
            }
            return redirect()->route('login')->withErrors(['error' => 'Votre session a expiré. Veuillez vous reconnecter.']);
        };

        // AuthenticationException (middleware auth standard)
        $exceptions->renderable(function (AuthenticationException $e, Request $request) use ($loginRedirect) {
            return $loginRedirect($request);
        });

        // ErrorException : "Attempt to read property on null" = session expirée
        $exceptions->renderable(function (\ErrorException $e, Request $request) use ($loginRedirect) {
            if (str_contains($e->getMessage(), 'Attempt to read property') || str_contains($e->getMessage(), 'on null')) {
                return $loginRedirect($request);
            }
        });

        // Session expirée → redirection vers la page de connexion
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Session expirée, veuillez vous reconnecter.'], 419);
            }
            return redirect()->route('login')
                ->withErrors(['error' => 'Votre session a expiré. Veuillez vous reconnecter.']);
        });

        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });
    })->create();
