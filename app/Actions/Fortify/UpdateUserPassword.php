<?php

namespace App\Actions\Fortify;

use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        $passwordRules = array_merge($this->passwordRules(), [
            function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                if (Hash::check($value, $user->password)) {
                    $fail('La nueva contraseña no puede ser idéntica a la contraseña actual.');
                }
            },
        ]);

        $validator = Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password'         => $passwordRules,
        ], [
            'current_password.current_password' => 'La contraseña actual ingresada es incorrecta.',
            'password.confirmed'                => 'La confirmación de la contraseña no coincide.',
            'password.min'                      => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            session()->flash('error', $validator->errors()->first());
            $validator->validateWithBag('updatePassword');
        }
        
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        // Regla 4: Alerta de cierre programado
        session()->flash('warning', 'Contraseña actualizada correctamente. Por seguridad, la sesión se cerrará en 5 segundos.');
        session()->flash('auto_logout', true);
    }
}
