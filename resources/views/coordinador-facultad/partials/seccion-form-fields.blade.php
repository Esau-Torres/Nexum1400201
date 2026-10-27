@php
    $isEdit = $seccion !== null;
    $sel = fn($field) => old($field, $isEdit ? $seccion->$field : null);

    $estadoSeleccionado = $sel('estado');
    if ($estadoSeleccionado instanceof \App\Enums\EstadoSeccion) {
        $estadoSeleccionado = $estadoSeleccionado->value;
    }
@endphp

<div class="row g-3">

    {{-- Fila 1: ciclo, materia, docente --}}
    <div class="col-md-4">
        <label class="form-label small fw-medium">Ciclo lectivo *</label>
        <select name="id_ciclo_lectivo" class="form-select" required
                x-model="cicloId" @change="cambioCiclo()">
            <option value="">Seleccione...</option>
            @foreach($ciclosLectivos as $c)
                <option value="{{ $c->ciclo_lectivo_id }}"
                    {{ (string) $sel('id_ciclo_lectivo') === (string) $c->ciclo_lectivo_id ? 'selected' : '' }}>
                    {{ $c->nombre_ciclo }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-medium">Materia *</label>
        <select name="id_materia" class="form-select" required>
            <option value="">Seleccione...</option>
            @foreach($materiasPorCarrera as $carrera => $materiasGrupo)
                <optgroup label="{{ $carrera }}">
                    @foreach($materiasGrupo as $pm)
                        @if($pm->materia)
                            <option value="{{ $pm->materia->materia_id }}"
                                {{ (string) $sel('id_materia') === (string) $pm->materia->materia_id ? 'selected' : '' }}>
                                Ciclo {{ $pm->ciclo_sugerido }} — {{ $pm->materia->nombre }}
                            </option>
                        @endif
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-medium">Docente *</label>
        <select name="id_docente" class="form-select" required>
            <option value="">Seleccione...</option>
            @foreach($docentes as $d)
                <option value="{{ $d->docente_id }}"
                    {{ (string) $sel('id_docente') === (string) $d->docente_id ? 'selected' : '' }}>
                    {{ $d->user->name ?? '' }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Fila 2: grupo --}}
    <div class="col-md-6">
        <label class="form-label small fw-medium">Grupo (opcional)</label>

        @if($isEdit && $seccion->horarios->isNotEmpty())
            {{-- Select disabled NO envía el valor → hidden lo respalda --}}
            <input type="hidden" name="id_grupo" value="{{ $seccion->id_grupo }}">
        @endif

        <select name="id_grupo" class="form-select"
                x-model="grupoId" @change="cambioGrupo()"
                @if($isEdit && $seccion->horarios->isNotEmpty()) disabled @endif>
            <option value="">Sin grupo (materia libre)</option>
            <template x-for="g in gruposFiltrados" :key="g.grupo_id">
                <option :value="g.grupo_id"
                        :selected="String(g.grupo_id) === String(grupoId)"
                        x-text="'Grupo ' + g.nombre"></option>
            </template>
        </select>

        @if($isEdit && $seccion->horarios->isNotEmpty())
            <div class="form-text small text-warning">
                <i class="bi bi-lock-fill me-1"></i>
                No se puede cambiar el grupo: la sección ya tiene bloques horarios asignados.
            </div>
        @else
            <div class="form-text small">
                Si elegís un grupo, sus bloques horarios se usarán automáticamente.
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-medium">Nombre de grupo *</label>
        <input type="text" name="nombre_grupo" class="form-control"
               value="{{ $sel('nombre_grupo') }}"
               maxlength="20" required
               x-model="nombreGrupo">

        <div class="form-text small">
            <span x-show="grupoId">Se autocompletó desde el grupo seleccionado.</span>
            <span x-show="!grupoId">Ej: A, B, K, Ñ.</span>
        </div>
    </div>

    {{-- Fila 3: cupos y estado --}}
    <div class="col-md-3">
        <label class="form-label small fw-medium">Cupo máximo *</label>
        <input type="number" name="cupo_maximo" class="form-control"
               value="{{ $sel('cupo_maximo') ?? 30 }}"
               min="1" max="200" required
               x-model.number="cupoMaximo">
    </div>

    <div class="col-md-3">
        <label class="form-label small fw-medium">Cupo extra máximo</label>
        <input type="number" name="cupo_extra_maximo" class="form-control"
               value="{{ $sel('cupo_extra_maximo') ?? 0 }}"
               min="0" max="3"
               x-model.number="cupoExtra"
               :readonly="!permiteSobrecupo"
               :class="{ 'bg-light text-muted': !permiteSobrecupo }">
        <div class="form-text small">
            <span x-show="permiteSobrecupo">Máximo 3 alumnos.</span>
            <span x-show="!permiteSobrecupo" class="text-muted">
                <i class="bi bi-lock me-1"></i> Requiere permitir sobrecupo.
            </span>
        </div>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch w-100">
            <input type="hidden" name="permite_sobrecupo" value="0">
            <input class="form-check-input" type="checkbox" name="permite_sobrecupo" value="1"
                   id="permite_sobrecupo-{{ $isEdit ? $seccion->seccion_id : 'new' }}"
                   x-model="permiteSobrecupo"
                   @change="cambioSobrecupo()">
            <label class="form-check-label small"
                   for="permite_sobrecupo-{{ $isEdit ? $seccion->seccion_id : 'new' }}">
                Permitir sobrecupo
            </label>
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label small fw-medium">Estado *</label>
        <select name="estado" class="form-select" required>
            @foreach($estados as $e)
                <option value="{{ $e->value }}"
                    {{ (string) $estadoSeleccionado === $e->value ? 'selected' : '' }}>
                    {{ $e->label() }}
                </option>
            @endforeach
        </select>
    </div>
</div>