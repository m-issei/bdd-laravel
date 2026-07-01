<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            return match (true) {
                str_starts_with($request->path(), 'super') => route('super.login'),
                str_starts_with($request->path(), 'admin') => route('admin.login'),
                str_starts_with($request->path(), 'app')   => route('app.login'),
                default => route('login'),
            };
        });

        $middleware->redirectUsersTo(function (Request $request) {
            return match (true) {
                str_starts_with($request->path(), 'super') => route('super.organizations.index'),
                str_starts_with($request->path(), 'admin') => route('admin.dashboard'),
                default => '/',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
