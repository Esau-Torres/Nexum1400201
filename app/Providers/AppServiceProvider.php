<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Failed::class, function (Failed $event) {
        Log::warning('Intento de autenticación fallido', [
                'ip' => request()->ip(),
                'usuario' => $event->credentials['email']
                    ?? $event->credentials['documento_identidad']
                    ?? 'desconocido',
                'guard' => $event->guard,
            ]);
        });
    }
}
