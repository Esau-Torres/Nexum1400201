<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoEgreso;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarEgresoAction
{
    public function __construct(
        private ValidarEgresoAction $validarAction,
    ) {}

    public function execute(Alumnos $alumno, User $admin): RequisitosEgreso
    {
        $validacion = $this->validarAction->execute($alumno);

        if (!$validacion['puede']) {
            throw ValidationException::withMessages([
                'egreso' => $validacion['bloqueadores'],
            ]);
        }

        return DB::transaction(function () use ($alumno, $admin) {
            $req = RequisitosEgreso::paraAlumno($alumno->alumno_id);
            $req->estado_egreso = EstadoEgreso::EGRESADO;
            $req->aprobado_por  = $admin->id;
            $req->fecha_egreso  = now()->toDateString();
            $req->save();

            return $req->fresh();
        });
    }
}