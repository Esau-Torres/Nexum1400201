<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Academico\ConfiguracionAcademica;
use App\Rules\Egreso\HorasSocialesDentroDelLimiteRule;
use App\Rules\Egreso\TieneArancelServicioSocialPagadoRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GestionarHorasSocialesAction
{
    public function execute(
        Alumnos $alumno,
        int $horasCompletadas,
        bool $horasAprobadas,
        ?UploadedFile $documento = null
    ): RequisitosEgreso {
        // ─────────────────────────────────────────────
        // 1. Validaciones (Rules)
        // ─────────────────────────────────────────────
        $validator = Validator::make(
            ['horas' => $horasCompletadas],
            [
                'horas' => [
                    new TieneArancelServicioSocialPagadoRule($alumno),
                    new HorasSocialesDentroDelLimiteRule($alumno),
                ],
            ]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'horas' => $validator->errors()->all(),
            ]);
        }

        // ─────────────────────────────────────────────
        // 2. Persistir
        // ─────────────────────────────────────────────
        return DB::transaction(function () use ($alumno, $horasCompletadas, $horasAprobadas, $documento) {
            $req = RequisitosEgreso::paraAlumno($alumno->alumno_id);

            $req->horas_sociales_completadas = $horasCompletadas;
            $req->horas_sociales_aprobadas   = $horasAprobadas;

            if ($documento) {
                if ($req->documento_constancia_social) {
                    Storage::disk('local')->delete($req->documento_constancia_social);
                }

                $path = $documento->store(
                    "documentos/proceso-egreso/{$alumno->alumno_id}",
                    'local'
                );
                $req->documento_constancia_social = $path;
            }

            $req->save();

            return $req->fresh();
        });
    }
}