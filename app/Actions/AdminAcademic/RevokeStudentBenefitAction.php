<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoBeneficio;
use App\Mail\BenefitRevokedMail;
use App\Models\Academico\BeneficioEstudiante;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class RevokeStudentBenefitAction
{
    public function execute(
        BeneficioEstudiante $beneficio,
        string $motivo,
        User $admin,
        string $origen = 'MANUAL'
    ): void {
        if ($beneficio->estado !== EstadoBeneficio::ACTIVO) {
            throw new RuntimeException('El beneficio ya no está activo.');
        }

        DB::transaction(function () use ($beneficio, $motivo, $admin, $origen) {
            $beneficio->update([
                'estado'            => EstadoBeneficio::REVOCADO,
                'motivo_revocacion' => "[{$origen}] {$motivo}",
                'revocado_por'      => $admin->id,
                'revocado_en'       => now(),
            ]);
        });

        $beneficio->refresh()->load(['alumno.usuario', 'alumno.carrera']);

        Mail::to($beneficio->alumno->usuario->email)
            ->send(new BenefitRevokedMail($beneficio, $motivo));
    }
}