<?php

namespace App\Http\Controllers\Estudiante;

use App\Actions\Student\RetirarMateriaAction;
use App\Http\Controllers\Controller;
use App\Models\Estudiante\DetalleInscripcion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RetirarMateriaController extends Controller
{
    public function retirar(Request $request, int $detalleId, RetirarMateriaAction $action)
    {
        $alumno = $request->user()->alumno;
        abort_if(!$alumno, 403);

        $data = $request->validate([
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        // Verificar ownership
        $detalle = DetalleInscripcion::where('detalle_inscripcion_id', $detalleId)
            ->whereHas('inscripcion', fn ($q) => $q->where('id_alumno', $alumno->alumno_id))
            ->firstOrFail();

        try {
            $action->execute(
                $alumno,
                $detalle,
                $request->user(),
                $data['motivo'] ?? null
            );

            return redirect()
                ->route('student.record.index')
                ->with('success', 'La materia fue retirada correctamente.');
        } catch (ValidationException $e) {
            return redirect()
                ->route('student.record.index')
                ->with('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('student.record.index')
                ->with('error', 'Ocurrió un error al procesar el retiro.');
        }
    }
}