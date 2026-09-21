<?php

namespace App\Providers;

use App\Models\Users\User;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Actions\Fortify\LoginUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse;

class FortifyServiceProvider extends ServiceProvider
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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        
        $this->app->bind(LoginResponse::class, LoginUser::class);
        
        $this->app->singleton(VerifyEmailResponse::class, function () {
            return new class implements VerifyEmailResponse {
                public function toResponse($request)
                {
                    return redirect()->intended(config('fortify.home'))
                        ->with('success', 'Dirección de correo institucional verificada correctamente.');
                }
            };
        });

        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse
            {
                public function toResponse($request)
                {
                    return redirect()
                        ->route('login')
                        ->with(
                            'info',
                            'Solicitud recibida. Tu expediente está en revisión por Administración Académica.'
                        );
                }
            };
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        Fortify::authenticateUsing(function (Request $request) {

            $user = User::where('email', $request->email)->first();

            // Usuario no existe o contraseña incorrecta
            if (!$user || !Hash::check($request->password, $user->password)) {

                session()->flash(
                    'error',
                    'Credenciales inválidas. Verifique su correo institucional y contraseña.'
                );

                session()->flash(
                    'error_title',
                    'Acceso Denegado'
                );

                throw ValidationException::withMessages([
                    Fortify::username() => ['Acceso no autorizado.'],
                ]);
            }

            // Usuario existe y contraseña correcta,
            // pero todavía no está habilitado
            if ($user->estado == 0) {

                session()->flash(
                    'info',
                    'Su cuenta se encuentra pendiente de aprobación por Administración Académica.'
                );

                session()->flash(
                    'error_title',
                    'Cuenta pendiente'
                );

                throw ValidationException::withMessages([
                    Fortify::username() => [
                        'Su cuenta aún no está habilitada.'
                    ],
                ]);
            }

               // 3. Usuario activo pero SIN roles activos asignados
            if (!$user->roles()->where('roles.estado', true)->exists()) {
                session()->flash(
                    'warning',
                    'Su cuenta está activa, pero no posee ningún permiso asignado. Contacte a Administración Académica para habilitar su acceso al sistema.'
                );
                session()->flash('error_title', 'Sin permisos asignados');

                throw ValidationException::withMessages([
                    Fortify::username() => ['Su cuenta no tiene roles activos asignados.'],
                ]);
            }

            // Usuario habilitado
            return $user;
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });

        // 2. Definición de Vistas Blade
        Fortify::loginView(fn () => view('welcome'));
        Fortify::verifyEmailView(function () { return view('auth.verify-email');});
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::confirmPasswordsUsing(function ($user, string $password) {
            $isValid = Hash::check($password, $user->password);

            if (! $isValid) {
                session()->flash('error', 'La contraseña ingresada no es válida. Verifique sus datos para continuar.');
            }

            return $isValid;
        });

    }
}
