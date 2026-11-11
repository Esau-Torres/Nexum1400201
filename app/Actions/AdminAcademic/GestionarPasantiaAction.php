<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GestionarPasantiaAction
{
    public function execute(
        Alumnos $alumno,
        bool $requierePasantia,
        bool $pasantiaAprobada,
        ?string $institucion,
        ?UploadedFile $documento = null
    ): RequisitosEgreso {
        return DB::transaction(function () use (
            $alumno, $requierePasantia, $pasantiaAprobada, $institucion, $documento
        ) {
            $req = RequisitosEgreso::paraAlumno($alumno->alumno_id);

            $req->requiere_pasantia    = $requierePasantia;
            $req->pasantia_aprobada    = $pasantiaAprobada;
            $req->institucion_pasantia = $institucion;

            if ($documento) {
                if ($req->documento_pasantia) {
                    Storage::disk('local')->delete($req->documento_pasantia);
                }

                $path = $documento->store(
                    "documentos/proceso-egreso/{$alumno->alumno_id}",
                    'local'
                );
                $req->documento_pasantia = $path;
            }

            $req->save();

            return $req->fresh();
        });
    }
}