<?php

use App\Exceptions\ExibivelException;
use App\Http\Middleware\VerificarTipo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tipo' => VerificarTipo::class,
        ]);
        $middleware->redirectUsersTo(fn ($request) => $request->user()->homeUrl());
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ExibivelException $exception) {
            if (request()->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['erro' => $exception->getMessage()]);
        });
    })->create();
