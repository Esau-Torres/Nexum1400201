<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\CambiarEstadoSituacionAction;
use App\Enums\EstadoSituacion;
use App\Enums\TipoSituacion;
use App\Http\Controllers\Controller;
use App\Models\Estudiante\SituacionUsuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SituacionGestionController extends Controller
{
    public function index(Request $request)
    {
        $situaciones = SituacionUsuario::with('usuario')
            ->when($request->filled('tipo'), fn ($q) =>
                $q->where('tipo_situacion', $request->tipo)
            )
            ->when($request->filled('estado'), fn ($q) =>
                $q->where('estado', $request->estado)
            )
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $term = '%' . trim($request->buscar) . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('nombre_usuario', 'ilike', $term)
                       ->orWhere('carnet_institucional', 'ilike', $term)
                       ->orWhere('correo', 'ilike', $term)
                       ->orWhere('detalle', 'ilike', $term);
                });
            })
            ->orderByRaw("CASE estado
                WHEN 'PENDIENTE' THEN 1
                WHEN 'EN_OBSERVACION' THEN 2
                WHEN 'CERRADO' THEN 3
                ELSE 4 END ASC")
            ->orderByDesc('creado_en')
            ->paginate(15)
            ->withQueryString();

        $tipos   = TipoSituacion::options();
        $estados = collect(EstadoSituacion::cases())
            ->mapWithKeys(fn ($e) => [$e->value => $e->label()])
            ->all();

        $estadosDestino = [
            EstadoSituacion::EN_OBSERVACION->value => EstadoSituacion::EN_OBSERVACION->label(),
            EstadoSituacion::CERRADO->value        => EstadoSituacion::CERRADO->label(),
        ];

        return view('adminacademico.situaciones_reponse', compact(
            'situaciones', 'tipos', 'estados', 'estadosDestino'
        ));
    }

    public function cambiarEstado(
        Request $request,
        int $id,
        CambiarEstadoSituacionAction $action
    ) {
        $data = $request->validate([
            'estado' => [
                'required',
                Rule::in([
                    EstadoSituacion::EN_OBSERVACION->value,
                    EstadoSituacion::CERRADO->value,
                ]),
            ],
        ], [
            'estado.required' => 'Debes seleccionar un estado.',
            'estado.in'       => 'El estado seleccionado no es válido.',
        ]);

        $situacion = SituacionUsuario::findOrFail($id);

        try {
            $action->execute(
                $situacion,
                EstadoSituacion::from($data['estado']),
                $request->user()
            );

            return redirect()
                ->route('admin-academico.situaciones.gestion')
                ->with('success', 'El estado de la situación se actualizó correctamente.');
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }
}