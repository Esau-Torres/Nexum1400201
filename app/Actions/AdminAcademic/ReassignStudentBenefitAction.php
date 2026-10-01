<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoBeneficio;
use App\Mail\BenefitReassignedMail;
use App\Models\Academico\BeneficioEstudiante;
use App\Models\Estudiante\Alumnos;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class ReassignStudentBenefitAction
{
    public function __construct(
        protected AssignStudentBenefitAction $assignBenefit,
    ) {}

    public function execute(
        Alumnos $alumno,
        BeneficioEstudiante $beneficioAnterior,
        array $nuevoBeneficio,
        User $admin,
        string $motivo = 'Reasignación de beneficio por decisión administrativa.'
    ): BeneficioEstudiante {
        if ($beneficioAnterior->estado !== EstadoBeneficio::ACTIVO) {
            throw new RuntimeException('El beneficio anterior no está activo.');
        }

        $nuevo = DB::transaction(function () use ($alumno, $beneficioAnterior, $nuevoBeneficio, $admin, $motivo) {

            // 1. Revocar el anterior
            $beneficioAnterior->update([
                'estado'            => EstadoBeneficio::REVOCADO,
                'motivo_revocacion' => "[REASIGNACION] {$motivo}",
                'revocado_por'      => $admin->id,
                'revocado_en'       => now(),
            ]);

            // 2. Crear el nuevo en el mismo ciclo
            return $this->assignBenefit->execute(
                alumno: $alumno,
                data: array_merge($nuevoBeneficio, [
                    'id_ciclo_lectivo' => $beneficioAnterior->id_ciclo_lectivo,
                ]),
                asignadoPor: $admin,
            );
        });

        // 3. Correo único de reasignación
        $nuevo->load(['alumno.usuario', 'alumno.carrera']);

        Mail::to($nuevo->alumno->usuario->email)
            ->send(new BenefitReassignedMail($nuevo, $beneficioAnterior, $motivo));

        return $nuevo;
    }
}