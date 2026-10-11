@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

  <!-- HEADER PRINCIPAL -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Título e Identidad -->
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon-circle bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem;">
                <i class="bi bi-building-gear"></i>
            </div>
            <div>
                <h1 class="h3 fw-bold text-dark mb-1 tracking-tight">GESTIÓN DE FACULTADES Y CARRERAS</h1>
                <p class="text-muted mb-0 small">Administración y control de la estructura académica institucional de la UMA.</p>
            </div>
        </div>

        <!-- Botón de Acción Global -->
        <div>
            <button type="button" class="btn btn-danger btn-sm px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm rounded-3 transition-all"
                    data-bs-toggle="modal" data-bs-target="#createFacultadModal">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span>Nueva Facultad</span>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm rounded-3 transition-all"
                    data-bs-toggle="modal" data-bs-target="#createCarreraModal">
                <i class="fas fa-graduation-cap fs-5"></i>
                <span>Nueva Carrera</span>
            </button>
        </div>
    </div>
</div>

    <!-- NAVEGACIÓN POR PESTAÑAS (TABS) -->
    @php
        $activeTab = request('tab', 'facultades');
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <ul class="nav custom-pills gap-2" role="tablist">
            {{-- Pestaña Facultades --}}
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'facultades' ? 'active' : '' }}"
                        data-bs-toggle="pill"
                        data-bs-target="#tab-facultades"
                        type="button" role="tab">
                    <i class="bi bi-building"></i>
                    <span>Facultades</span>
                    <span class="badge ms-1 rounded-pill
                        {{ $activeTab === 'facultades'
                            ? 'text-bg-danger'
                            : 'text-bg-light text-dark border' }}">
                        {{ $facultades->count() }}
                    </span>
                </button>
            </li>

            {{-- Pestaña Carreras --}}
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'carreras' ? 'active' : '' }}"
                        data-bs-toggle="pill"
                        data-bs-target="#tab-carreras"
                        type="button" role="tab">
                    <i class="bi bi-mortarboard"></i>
                    <span>Carreras</span>
                    <span class="badge ms-1 rounded-pill
                        {{ $activeTab === 'carreras'
                            ? 'text-bg-danger'
                            : 'text-bg-light text-dark border' }}">
                        {{ $carreras->count() }}
                    </span>
                </button>
            </li>
        </ul>
    </div>
    <div class="tab-content">

        {{-- ============================================ --}}
        {{-- TAB FACULTADES --}}
        {{-- ============================================ --}}
        <div class="tab-pane fade {{ $activeTab === 'facultades' ? 'show active' : '' }}" id="tab-facultades">
            {{-- Tabla --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div>
                        <table class="table align-middle mb-0" style="font-size: 0.9rem;" id="facultadesTable">
                            <thead style="background-color: #fafbfc; border-bottom: 2px solid #eef0f2;">
                                <tr>
                                    <th class="px-4 py-3 text-secondary small fw-semibold text-uppercase">Facultad</th>
                                    <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">Carreras</th>
                                    <th class="py-3 text-end px-4 text-secondary small fw-semibold text-uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($facultades as $facultad)
                                    <tr style="border-bottom: 1px solid #f1f3f5;">
                                        <td class="px-4 py-3 fw-medium text-dark">{{ $facultad->nombre }}</td>
                                        <td class="py-3 text-center">
                                            <span class="badge text-bg-light border">{{ $facultad->carreras_count }}</span>
                                        </td>
                                        <td class="py-3 text-end px-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editFacultadModal-{{ $facultad->facultad_id }}"
                                                    title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            {{-- Botón eliminar: abre modal de confirmación --}}
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    title="Eliminar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteFacultadModal-{{ $facultad->facultad_id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-building fs-2 d-block mb-2"></i>
                                            No hay facultades registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal crear facultad --}}
            <div class="modal fade" id="createFacultadModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('admin-academico.facultades.facultad.store') }}" method="POST">
                            @csrf
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-plus-circle-fill text-danger me-2"></i>Nueva Facultad
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body pt-2">
                                <label class="form-label small fw-semibold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="nombre"
                                       value="{{ old('nombre') }}"
                                       maxlength="150" required
                                       placeholder="Ej: Facultad de Medicina">
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-check-circle me-1"></i>Crear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modales editar y eliminar facultad --}}
            @foreach ($facultades as $facultad)
                {{-- EDITAR --}}
                <div class="modal fade" id="editFacultadModal-{{ $facultad->facultad_id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <form action="{{ route('admin-academico.facultades.facultad.update', $facultad) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-pencil-square text-danger me-2"></i>Editar Facultad
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <label class="form-label small fw-semibold">
                                        Nombre <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="nombre"
                                           value="{{ $facultad->nombre }}"
                                           maxlength="150" required>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-check-circle me-1"></i>Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ELIMINAR --}}
                <div class="modal fade" id="deleteFacultadModal-{{ $facultad->facultad_id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Eliminación
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">
                                    ¿Está seguro de eliminar la facultad
                                    <strong>{{ $facultad->nombre }}</strong>?
                                </p>
                                @if ($facultad->carreras_count > 0)
                                    <div class="alert alert-warning small mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Esta facultad tiene <strong>{{ $facultad->carreras_count }}</strong>
                                        carrera(s) vinculada(s). Debe eliminarlas o reasignarlas primero.
                                    </div>
                                @else
                                    <div class="alert alert-danger small mb-0">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Esta acción es <strong>irreversible</strong>.
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <form action="{{ route('admin-academico.facultades.facultad.delete', $facultad) }}"
                                      method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============================================ --}}
        {{-- TAB CARRERAS --}}
        {{-- ============================================ --}}
        <div class="tab-pane fade {{ $activeTab === 'carreras' ? 'show active' : '' }}" id="tab-carreras">

            {{-- Filtros + botón --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin-academico.facultades.facultad') }}"
                          class="d-flex flex-wrap align-items-end gap-3 justify-content-end">
                        {{-- Fuerza el tab carreras --}}
                        <input type="hidden" name="tab" value="carreras">

                        <div style="min-width: 200px;">
                            <label class="form-label small fw-semibold text-secondary mb-1">Facultad</label>
                            <select name="id_facultad" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach ($facultades as $f)
                                    <option value="{{ $f->facultad_id }}" @selected(request('id_facultad') == $f->facultad_id)>
                                        {{ $f->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="min-width: 180px;">
                            <label class="form-label small fw-semibold text-secondary mb-1">Nivel</label>
                            <select name="id_nivel_academico" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach ($niveles as $n)
                                    <option value="{{ $n->nivel_academico_id }}" @selected(request('id_nivel_academico') == $n->nivel_academico_id)>
                                        {{ $n->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-funnel-fill me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('admin-academico.facultades.facultad', ['tab' => 'carreras']) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Limpiar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla carreras --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div>
                        <table class="table align-middle mb-0" style="font-size: 0.9rem;" id="carrerasTable">
                            <thead style="background-color: #fafbfc; border-bottom: 2px solid #eef0f2;">
                                <tr>
                                    <th class="px-4 py-3 text-secondary small fw-semibold text-uppercase">Carrera</th>
                                    <th class="py-3 text-secondary small fw-semibold text-uppercase">Facultad</th>
                                    <th class="py-3 text-secondary small fw-semibold text-uppercase">Nivel</th>
                                    <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">Ciclos</th>
                                    <th class="py-3 text-center text-secondary small fw-semibold text-uppercase">Pensums</th>
                                    <th class="py-3 text-end px-4 text-secondary small fw-semibold text-uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($carreras as $carrera)
                                    @php
                                        $tienePensum = $carrera->pensums_count > 0;
                                        $tieneAlumnos = $carrera->alumno()->exists();
                                        $bloqueoEliminar = $tienePensum || $tieneAlumnos;
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f1f3f5;">
                                        <td class="px-4 py-3 fw-medium text-dark">{{ $carrera->nombre }}</td>
                                        <td class="py-3 text-muted small">{{ $carrera->facultad?->nombre ?? '—' }}</td>
                                        <td class="py-3">
                                            <span class="badge text-bg-light border small">
                                                {{ $carrera->nivel?->nombre ?? $carrera->nivel_academico }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="badge text-bg-light border">{{ $carrera->duracion_ciclos }}</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="badge {{ $tienePensum ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                {{ $carrera->pensums_count }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-end px-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editCarreraModal-{{ $carrera->carrera_id }}"
                                                    title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    title="{{ $bloqueoEliminar ? 'No se puede eliminar (tiene pensums o alumnos)' : 'Eliminar' }}"
                                                    @disabled($bloqueoEliminar)
                                                    @if(!$bloqueoEliminar)
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteCarreraModal-{{ $carrera->carrera_id }}"
                                                    @endif>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-mortarboard fs-2 d-block mb-2"></i>
                                            No hay carreras registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal crear carrera --}}
            <div class="modal fade" id="createCarreraModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('admin-academico.facultades.carrera.store') }}" method="POST">
                            @csrf
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-plus-circle-fill text-danger me-2"></i>Nueva Carrera
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body pt-2">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">
                                        Facultad <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_facultad" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        @foreach ($facultades as $f)
                                            <option value="{{ $f->facultad_id }}">{{ $f->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">
                                        Nivel Académico <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_nivel_academico" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        @foreach ($niveles as $n)
                                            <option value="{{ $n->nivel_academico_id }}">{{ $n->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">
                                        Nombre <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="nombre"
                                           maxlength="150" required
                                           placeholder="Ej: Licenciatura en Medicina">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-semibold">
                                        Duración en Ciclos <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="duracion_ciclos" class="form-control"
                                           min="1" max="20" value="10" required>
                                    <div class="form-text small">Número entero entre 1 y 20.</div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-check-circle me-1"></i>Crear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modales editar y eliminar carrera --}}
            @foreach ($carreras as $carrera)
                @php
                    $tienePensum = $carrera->pensums_count > 0;
                    $tieneAlumnos = $carrera->alumno()->exists();
                    $bloqueoEliminar = $tienePensum || $tieneAlumnos;
                @endphp

                {{-- EDITAR --}}
                <div class="modal fade" id="editCarreraModal-{{ $carrera->carrera_id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <form action="{{ route('admin-academico.facultades.carrera.update', $carrera) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-pencil-square text-danger me-2"></i>Editar Carrera
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body pt-2">

                                    @if ($tienePensum)
                                        <div class="alert alert-warning small py-2 px-3 mb-3">
                                            <i class="bi bi-lock-fill me-1"></i>
                                            Esta carrera tiene pensums vinculados: no se puede cambiar la <strong>duración de ciclos</strong>.
                                        </div>
                                    @endif

                                    @if ($tieneAlumnos)
                                        <div class="alert alert-warning small py-2 px-3 mb-3">
                                            <i class="bi bi-people-fill me-1"></i>
                                            Esta carrera tiene estudiantes: no se puede cambiar de <strong>facultad</strong>.
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">
                                            Facultad <span class="text-danger">*</span>
                                        </label>
                                        <select name="id_facultad" class="form-select" required
                                                @disabled($tieneAlumnos)>
                                            @foreach ($facultades as $f)
                                                <option value="{{ $f->facultad_id }}"
                                                        @selected($carrera->id_facultad == $f->facultad_id)>
                                                    {{ $f->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($tieneAlumnos)
                                            <input type="hidden" name="id_facultad" value="{{ $carrera->id_facultad }}">
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">
                                            Nivel Académico <span class="text-danger">*</span>
                                        </label>
                                        <select name="id_nivel_academico" class="form-select" required>
                                            @foreach ($niveles as $n)
                                                <option value="{{ $n->nivel_academico_id }}"
                                                        @selected($carrera->id_nivel_academico == $n->nivel_academico_id)>
                                                    {{ $n->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">
                                            Nombre <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="nombre"
                                               value="{{ $carrera->nombre }}"
                                               maxlength="150" required>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label small fw-semibold">
                                            Duración en Ciclos <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="duracion_ciclos" class="form-control"
                                               min="1" max="20"
                                               value="{{ $carrera->duracion_ciclos }}"
                                               @disabled($tienePensum)
                                               required>
                                        @if ($tienePensum)
                                            <input type="hidden" name="duracion_ciclos" value="{{ $carrera->duracion_ciclos }}">
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-check-circle me-1"></i>Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ELIMINAR --}}
                @if (!$bloqueoEliminar)
                    <div class="modal fade" id="deleteCarreraModal-{{ $carrera->carrera_id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold text-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Eliminación
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2">
                                        ¿Está seguro de eliminar la carrera
                                        <strong>{{ $carrera->nombre }}</strong>
                                        de la facultad <strong>{{ $carrera->facultad?->nombre }}</strong>?
                                    </p>
                                    <div class="alert alert-danger small mb-0">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Esta acción es <strong>irreversible</strong>.
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                    <form action="{{ route('admin-academico.facultades.carrera.delete', $carrera) }}"
                                          method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash me-1"></i>Sí, eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection