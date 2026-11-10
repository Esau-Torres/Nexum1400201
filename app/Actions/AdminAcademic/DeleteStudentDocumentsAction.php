<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Users\User;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteStudentDocumentsAction
{
    private const CAMPOS_VALIDOS = [
        'titulo_bachillerato',
        'partida_nacimiento',
        'fotografia_personal',
        'constancia_paes',
    ];

    private const CAMPOS_NO_ELIMINABLES = [
     'fotografia_personal',
     'partida_nacimiento',
    ];

    private const DISCO_PUBLICO = ['fotografia_personal'];

    public function execute(Alumnos $alumno, string $campo, User $admin): void
    {
        if (!in_array($campo, self::CAMPOS_VALIDOS, true)) {
            throw new RuntimeException("Campo '{$campo}' no es válido.");
        }

        if (!$admin->hasRole('ADMIN_ACADEMICO')) {
            throw new RuntimeException('No tiene permisos para eliminar documentos.');
        }

        if (in_array($campo, self::CAMPOS_NO_ELIMINABLES, true)) {
            throw new RuntimeException("El documento '{$campo}' no se puede eliminar.");
        }

        $documentos = $alumno->documentos;
        if (!$documentos || !$documentos->$campo) {
            throw new RuntimeException('El documento no existe.');
        }

        $disco = in_array($campo, self::DISCO_PUBLICO, true) ? 'public' : 'local';
        $ruta  = $documentos->$campo;

        // 1. Borrar archivo físico
        if (Storage::disk($disco)->exists($ruta)) {
            Storage::disk($disco)->delete($ruta);
        }

        // 2. Actualizar BD
        $documentos->update([$campo => null]);

        //recalcular estado despues de eliminar 
        $documentos->refresh();
        $documentos->recalcularEstado();

        // 3. Borrar carpetas vacías
        $this->limpiarCarpetasVacias($alumno);
    }

    private function limpiarCarpetasVacias(Alumnos $alumno): void
    {
        $carpetas = [
            'local'  => "documentos/alumnos-ingreso/{$alumno->alumno_id}",
            'public' => "estudiantes/imagenes/{$alumno->alumno_id}",
        ];

        foreach ($carpetas as $disco => $carpeta) {
            $discoActual = Storage::disk($disco);

            if ($discoActual->exists($carpeta) && empty($discoActual->files($carpeta))) {
                $discoActual->deleteDirectory($carpeta);
            }
        }
    }
}
