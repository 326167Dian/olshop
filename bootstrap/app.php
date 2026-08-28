<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\PreventBackHistory::class,
        ]);
        $middleware->alias([
            'is.customer' => \App\Http\Middleware\IsCustomer::class,
            'prevent.back.history' => \App\Http\Middleware\PreventBackHistory::class,
            'admin.active' => \App\Http\Middleware\EnsureAdminActive::class,
            'inventory.module' => \App\Http\Middleware\EnsureInventoryModuleAccess::class,
        ]);
        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('backend/*') || $request->is('inventory') || $request->is('inventory/*')
                ? route('admin.login.form')
                : route('login.form');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
