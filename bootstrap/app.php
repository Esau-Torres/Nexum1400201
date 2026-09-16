<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            $modulesPath = base_path('routes/modules');

            if (! File::isDirectory($modulesPath)) {
                return;
            }

            foreach (File::allFiles($modulesPath) as $file) {
                // Nombre del archivo sin extensión → se usa como prefijo y nombre
                $module = $file->getFilenameWithoutExtension();

                Route::middleware('web')
                    ->prefix($module)          // ej: /superadmin, /admin, /financiera
                    ->name($module.'.')        // ej: superadmin.dashboard, users.index
                    ->group($file->getPathname());
            }
        },

    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
