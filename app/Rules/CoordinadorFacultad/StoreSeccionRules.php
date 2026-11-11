<?php

namespace App\Rules\CoordinadorFacultad;

use App\Enums\EstadoSeccion;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\CoordinadorFacultad\Grupo;
use App\Models\CoordinadorFacultad\ClaseCompartida;
use App\Models\Users\Role;
use App\Models\CoordinadorFacultad\Aula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\DB;

class StoreSeccionRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::COORDINADOR_FACULTAD) ?? false;
    }

public function rules(): array
    {
        $seccionParam = $this->route('seccion');
        $seccionId = is_object($seccionParam) ? $seccionParam->seccion_id : (is_numeric($seccionParam) ? $seccionParam : null);

        return [
            'id_materia'       => ['required', 'integer', 'exists:materias,materia_id'],
            'id_ciclo_lectivo' => ['required', 'integer', 'exists:ciclo_lectivo,ciclo_lectivo_id'],
            'id_docente'       => ['required', 'integer', 'exists:docentes,docente_id'],
            'id_grupo'         => ['nullable', 'integer', 'exists:grupo,grupo_id'],

            'nombre_grupo'     => [
                'required', 'string', 'max:20',
                'regex:/^[A-ZÑ0-9\-]+$/i',
                Rule::unique('seccion', 'nombre_grupo')
                    ->where('id_materia', $this->input('id_materia'))
                    ->where('id_ciclo_lectivo', $this->input('id_ciclo_lectivo'))
                    ->ignore($seccionId, 'seccion_id'),
            ],

            'cupo_maximo'       => ['required', 'integer', 'min:1', 'max:200'],
            'cupo_extra_maximo' => ['nullable', 'integer', 'min:0', 'max:3'],
            'permite_sobrecupo' => ['required', 'boolean'],
            'estado'            => ['required', new Enum(EstadoSeccion::class)],

            'aulas_por_horario'   => ['nullable', 'array'],
            'aulas_por_horario.*' => ['integer', 'exists:aula,aula_id'],

            'horarios'              => ['nullable', 'array'],
            'horarios.*.id_horario' => ['required_with:horarios.*.id_aula', 'integer', 'exists:horarios,horario_id'],
            'horarios.*.id_aula'    => ['required_with:horarios.*.id_horario', 'integer', 'exists:aula,aula_id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $idGrupo     = $this->input('id_grupo');
            $idDocente   = $this->input('id_docente');
            $idCiclo     = $this->input('id_ciclo_lectivo');
            $cupoMaximo  = (int) $this->input('cupo_maximo');
            $permite     = filter_var($this->input('permite_sobrecupo', false), FILTER_VALIDATE_BOOLEAN);
            $cupoExtra   = (int) $this->input('cupo_extra_maximo', 0);
            $nombreGrupo = $this->input('nombre_grupo');
            
            // FIX Seguro
            $seccionParam = $this->route('seccion');
            $seccionId    = is_object($seccionParam) ? $seccionParam->seccion_id : $seccionParam;
            $esUpdate     = !empty($seccionId);

            if ($permite && $cupoExtra === 0) {
                $v->errors()->add('cupo_extra_maximo', 'Si permitís sobrecupo, el cupo extra debe ser mayor a 0.');
            }
            if (!$permite && $cupoExtra > 0) {
                $v->errors()->add('cupo_extra_maximo', 'No podés definir cupo extra sin permitir sobrecupo.');
            }

            if (!$esUpdate) {
                $bloques = [];
                if ($idGrupo) {
                    $grupo = Grupo::with('horarios')->find($idGrupo);
                    $aulasPorHorario = $this->input('aulas_por_horario', []);
                    foreach ($grupo?->horarios ?? [] as $h) {
                        $idAula = $aulasPorHorario[$h->horario_id] ?? null;
                        if (!$idAula) {
                            // FIX: Previene fallo "Call to a member function on string"
                            $diaText = is_object($h->dia) && method_exists($h->dia, 'label') ? $h->dia->label() : (is_object($h->dia) ? $h->dia->value : $h->dia);
                            $v->errors()->add(
                                'aulas_por_horario',
                                "Falta asignar un aula para el bloque {$diaText} {$h->hora_inicio}–{$h->hora_fin}."
                            );
                            continue;
                        }
                        $bloques[] = ['id_horario' => $h->horario_id, 'id_aula' => (int) $idAula];
                    }
                } else {
                    foreach ($this->input('horarios', []) as $h) {
                        if (!empty($h['id_horario']) && !empty($h['id_aula'])) {
                            $bloques[] = ['id_horario' => (int) $h['id_horario'], 'id_aula' => (int) $h['id_aula']];
                        }
                    }
                    if (empty($bloques)) {
                        $v->errors()->add('horarios', 'Debés asignar al menos un bloque horario (o elegir un grupo).');
                    }
                }

                foreach ($bloques as $b) {
                    $aula = Aula::find($b['id_aula']);
                    if (!$aula) continue;

                    $clase = ClaseCompartida::query()
                        ->where('id_docente', $idDocente)
                        ->where('id_ciclo_lectivo', $idCiclo)
                        ->where('id_horario', $b['id_horario'])
                        ->first();

                    if ($clase) {
                        $cupoTotal = $clase->cupo_total + $cupoMaximo;
                        if ($cupoTotal > $aula->capacidad) {
                            $v->errors()->add('cupo_maximo', "El aula excede su capacidad. Cupo de clase compartida excedido.");
                        }
                        if ((int) $clase->id_aula !== (int) $b['id_aula']) {
                            $v->errors()->add('aulas_por_horario', 'El docente ya tiene una clase en otra aula en este bloque.');
                        }
                    } else {
                        if ($aula->capacidad < $cupoMaximo) {
                            $v->errors()->add('cupo_maximo', "El aula '{$aula->nombre_aula}' tiene capacidad {$aula->capacidad}.");
                        }
                    }
                }

                $horariosIds = array_column($bloques, 'id_horario');
                if ($nombreGrupo && !empty($horariosIds)) {
                    $choqueGrupo = DB::table('seccion_horario as sh')
                        ->join('seccion as s', 's.seccion_id', '=', 'sh.id_seccion')
                        ->where('s.id_ciclo_lectivo', $idCiclo)
                        ->where('s.nombre_grupo', $nombreGrupo)
                        ->whereIn('sh.id_horario', $horariosIds)
                        ->when($seccionId, fn ($q) => $q->where('s.seccion_id', '!=', $seccionId))
                        ->exists();

                    if ($choqueGrupo) {
                        $v->errors()->add('nombre_grupo', 'El grupo ya tiene otra materia en uno de esos bloques horarios.');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_materia.required'       => 'Seleccioná una materia.',
            'id_ciclo_lectivo.required' => 'Seleccioná un ciclo lectivo.',
            'id_docente.required'       => 'Seleccioná un docente.',
            'nombre_grupo.required'     => 'El nombre del grupo es obligatorio.',
            'nombre_grupo.unique'       => 'Ya existe una sección de esta materia con ese grupo en este ciclo.',
            'nombre_grupo.regex'        => 'El nombre del grupo solo admite letras, números y guiones.',
        ];
    }
}