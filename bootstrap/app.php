<?php

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
            'sams2.auth' => \App\Http\Middleware\EnsureSams2Auth::class,
            'sams2.mega_admin' => \App\Http\Middleware\EnsureMegaAdmin::class,
            'sams2.admin' => \App\Http\Middleware\EnsureAdmin::class,
            'sams2.module' => \App\Http\Middleware\CheckModulePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
