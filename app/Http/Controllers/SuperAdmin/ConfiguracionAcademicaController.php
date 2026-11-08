<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Actions\SuperAdmin\ActualizarConfiguracionAcademicaAction;
use App\Http\Controllers\Controller;
use App\Models\Academico\ConfiguracionAcademica;
use App\Models\Estudiante\Carreras;
use App\Models\Academico\CicloLectivo;
use App\Jobs\CerrarCicloLectivoJob;
use App\Enums\EstadoCiclo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfiguracionAcademicaController extends Controller
{
    public function index()
    {
        $carreras = Carreras::with('facultad')
            ->orderBy('nombre')
            ->get();

        $configs = ConfiguracionAcademica::all()->keyBy('id_carrera');

        $cicloAbierto = CicloLectivo::whereIn('estado', [
                EstadoCiclo::MATRICULA_ABIERTA->value,
                EstadoCiclo::ACTIVO->value,
            ])
            ->orderByDesc('fecha_inicio')
            ->first();

        // Si no hay ciclo en esos estados, mostrar el más reciente para contexto
        $cicloMasReciente = $cicloAbierto ?? CicloLectivo::orderByDesc('fecha_inicio')->first();

        return view('superadmin.manage-ruler', compact(
            'carreras',
            'configs',
            'cicloAbierto',
            'cicloMasReciente'
        ));
    }

    public function update(Request $request, int $idCarrera, ActualizarConfiguracionAcademicaAction $action)
    {
        $carrera = Carreras::findOrFail($idCarrera);

        $data = $request->validate([
            'nota_minima_aprobatoria'      => ['required', 'numeric', 'between:0,10'],
            'max_reprobaciones_materia'    => ['required', 'integer', 'between:1,10'],
            'cum_minimo_materia_extra'     => ['required', 'numeric', 'between:0,10'],
            'uv_requeridas_horas_sociales' => ['required', 'integer', 'between:0,1000'],
            'uv_totales_egreso'            => ['required', 'integer', 'min:100'],
        ], [
            'nota_minima_aprobatoria.between'      => 'La nota mínima debe estar entre 0 y 10.',
            'max_reprobaciones_materia.between'    => 'El máximo de reprobaciones debe estar entre 1 y 10.',
            'cum_minimo_materia_extra.between'     => 'El CUM mínimo debe estar entre 0 y 10.',
            'uv_requeridas_horas_sociales.between' => 'Las UV de horas sociales deben estar entre 0 y 1000.',
            'uv_totales_egreso.min'                => 'Las UV totales de egreso deben ser al menos 100.',
        ]);

        $action->execute($idCarrera, $data);

        return redirect()
            ->route('superadmin.manage-ruler')
            ->with('success', "Configuración actualizada para {$carrera->nombre}.");
    }

    public function cerrar(Request $request, int $id)
    {
        $ciclo = CicloLectivo::findOrFail($id);

        if ($ciclo->estado === EstadoCiclo::CERRADO) {
            return redirect()
                ->route('superadmin.manage-ruler')
                ->with('error', 'El ciclo ya está cerrado.');
        }

        CerrarCicloLectivoJob::dispatch($ciclo->ciclo_lectivo_id);

        return redirect()
            ->route('superadmin.manage-ruler')
            ->with('success', "El cierre del ciclo {$ciclo->nombre_ciclo} se está procesando. Te notificaremos al finalizar.");
    }
    public function store(Request $request, ActualizarConfiguracionAcademicaAction $action)
    {
        $data = $request->validate([
            'id_carrera'                   => ['required', 'integer', 'exists:carreras,carrera_id'],
            'nota_minima_aprobatoria'      => ['required', 'numeric', 'between:0,10'],
            'max_reprobaciones_materia'    => ['required', 'integer', 'between:1,10'],
            'cum_minimo_materia_extra'     => ['required', 'numeric', 'between:0,10'],
            'uv_requeridas_horas_sociales' => ['required', 'integer', 'between:0,1000'],
            'uv_totales_egreso'            => ['required', 'integer', 'min:100'],
        ]);

        $existe = ConfiguracionAcademica::where('id_carrera', $data['id_carrera'])->exists();
        if ($existe) {
            return back()->with('error', 'Esta carrera ya tiene una configuración. Edítala en su lugar.');
        }

        $action->execute($data['id_carrera'], $data);

        return redirect()
            ->route('superadmin.manage-ruler')
            ->with('success', 'Configuración creada correctamente.');
    }
}