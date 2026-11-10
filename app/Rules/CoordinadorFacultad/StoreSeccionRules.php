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

class StoreSeccionRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::COORDINADOR_FACULTAD) ?? false;
    }

    public function rules(): array
    {
        $seccionId = $this->route('seccion')?->seccion_id;

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

            // Modo grupo: un aula por cada horario del grupo
            'aulas_por_horario'   => ['nullable', 'array'],
            'aulas_por_horario.*' => ['integer', 'exists:aula,aula_id'],

            // Modo libre: pares horario+aula
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
            $permite     = (bool) $this->input('permite_sobrecupo', false);
            $cupoExtra   = (int) $this->input('cupo_extra_maximo', 0);
            $nombreGrupo = $this->input('nombre_grupo');
            $seccionId   = $this->route('seccion')?->seccion_id;
            $seccion     = $this->route('seccion'); 
            $esUpdate    = $seccion !== null;

            // === Validación de sobrecupo ===
            if ($permite && $cupoExtra === 0) {
                $v->errors()->add('cupo_extra_maximo', 'Si permitís sobrecupo, el cupo extra debe ser mayor a 0.');
            }
            if (!$permite && $cupoExtra > 0) {
                $v->errors()->add('cupo_extra_maximo', 'No podés definir cupo extra sin permitir sobrecupo.');
            }

            // Resolver bloques+aulas
            if(!$esUpdate){
                $bloques = [];
                if ($idGrupo) {
                    $grupo = Grupo::with('horarios')->find($idGrupo);
                    $aulasPorHorario = $this->input('aulas_por_horario', []);
                    foreach ($grupo?->horarios ?? [] as $h) {
                        $idAula = $aulasPorHorario[$h->horario_id] ?? null;
                        if (!$idAula) {
                            $v->errors()->add(
                                'aulas_por_horario',
                                "Falta asignar un aula para el bloque {$h->dia->label()} {$h->hora_inicio}–{$h->hora_fin}."
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

                // === Validación de capacidad + clases compartidas ===
                foreach ($bloques as $b) {
                    $aula = Aula::find($b['id_aula']);
                    if (!$aula) continue;

                    // Buscar clase compartida existente para este bloque + docente
                    $clase = ClaseCompartida::query()
                        ->where('id_docente', $idDocente)
                        ->where('id_ciclo_lectivo', $idCiclo)
                        ->where('id_horario', $b['id_horario'])
                        ->first();

                    if ($clase) {
                        // Sumar cupos de las secciones existentes + la nueva
                        $cupoExistente = $clase->cupo_total;
                        $cupoTotal = $cupoExistente + $cupoMaximo;

                        if ($cupoTotal > $aula->capacidad) {
                            $v->errors()->add(
                                'cupo_maximo',
                                "La clase ya tiene {$cupoExistente} alumnos en el aula '{$aula->nombre_aula}' (cap. {$aula->capacidad}). Sumar {$cupoMaximo} excede la capacidad."
                            );
                        }

                        // Validar que la clase existente use la misma aula
                        if ((int) $clase->id_aula !== (int) $b['id_aula']) {
                            $v->errors()->add(
                                'aulas_por_horario',
                                'El docente ya tiene una clase compartida en ese bloque con OTRA aula. Usá la misma aula para compartir.'
                            );
                        }
                    } else {
                        // Clase nueva: solo validar que el aula alcance para el cupo nuevo
                        if ($aula->capacidad < $cupoMaximo) {
                            $v->errors()->add(
                                'cupo_maximo',
                                "El aula '{$aula->nombre_aula}' tiene capacidad {$aula->capacidad} y el cupo es {$cupoMaximo}."
                            );
                        }
                    }
                }

                // === Validación de choque de grupo ===
                $horariosIds = array_column($bloques, 'id_horario');
                if ($nombreGrupo && !empty($horariosIds)) {
                    $choqueGrupo = \DB::table('seccion_horario as sh')
                        ->join('seccion as s', 's.seccion_id', '=', 'sh.id_seccion')
                        ->where('s.id_ciclo_lectivo', $idCiclo)
                        ->where('s.nombre_grupo', $nombreGrupo)
                        ->whereIn('sh.id_horario', $horariosIds)
                        ->when($seccionId, fn ($q) => $q->where('s.seccion_id', '!=', $seccionId))
                        ->exists();

                    if ($choqueGrupo) {
                        $v->errors()->add(
                            'nombre_grupo',
                            'El grupo ya tiene otra materia en uno de esos bloques horarios.'
                        );
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
            'cupo_maximo.min'           => 'El cupo máximo debe ser mayor a 0.',
            'cupo_extra_maximo.max'     => 'El cupo extra no puede superar 3 alumnos.',
        ];
    }
}