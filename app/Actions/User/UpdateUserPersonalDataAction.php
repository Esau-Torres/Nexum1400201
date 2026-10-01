<?php

namespace App\Actions\User;

use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUserPersonalDataAction
{
    /**
     * @throws Exception
     */
    public function execute(int $userId, array $attributes): User
    {
        try {
            return DB::transaction(function () use ($userId, $attributes) {
                $user = User::lockForUpdate()->findOrFail($userId);

                $dataToUpdate =[
                    'name'               => $attributes['name'],
                    'email'              => $attributes['email'],
                    'celular'            => $attributes['celular'] ?? null,
                    'documento_identidad'=> $attributes['documento_identidad'] ?? null,
                    'fecha_nacimiento'   => $attributes['fecha_nacimiento'],
                    'id_regional_activo' => $attributes['id_regional_activo'],
                    'direccion'          => $attributes['direccion'] ?? null,
                ];

                // Si no es el usuario logeado, se permite actualizar el estado
                if (auth()->id() !== $userId && isset($attributes['estado'])) {
                    $dataToUpdate['estado'] = (int) $attributes['estado'];
                }

                $user->update($dataToUpdate);

                return $user;
                
            });
        } catch (Exception $e) {
            Log::error("[NEXUM_SECURITY] Error al actualizar usuario ID {$userId}: " . $e->getMessage());
            throw new Exception('Ocurrió una inconsistencia transaccional al persistir en la base de datos.');
        }
    }
}