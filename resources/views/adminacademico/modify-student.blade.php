@extends('layouts.app')
@section('title', 'Modificar Estudiantes')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width: 48px; height: 48px;">
                <i class="bi bi-person-gear fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">MODIFICAR ESTUDIANTE</h1>
                <p class="text-muted mb-0 small">Edición de datos personales y gestión documental</p>
            </div>
        </div>
        <a href="{{ route('home') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
            <i class="fa-solid fa-home me-1"></i> Inicio
        </a>
    </div>

    {{-- ============================================================
         VISTA PC: TABLA
         ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4 d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="modify-student-Table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Estudiante</th>
                        <th>Carnet</th>
                        <th>Email</th>
                        <th>Carrera</th>
                        <th>Documentos</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        @php
                            $usuario = $alumno->usuario;
                            $docs    = $alumno->documentos;
                            $uid     = $alumno->alumno_id;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-danger"
                                         style="width: 40px; height: 40px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($usuario->name ?? 'XX', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $usuario->name }}</div>
                                        <small class="text-muted">{{ $usuario->celular ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="font-monospace fw-medium">{{ $alumno->codigo_estudiante }}</span></td>
                            <td>
                                <div class="small">{{ $usuario->email }}</div>
                                <small class="text-muted">{{ $alumno->correo_institucional }}</small>
                            </td>
                            <td class="small">{{ $alumno->carrera->nombre ?? '—' }}</td>
                            <td>
                                @if($docs)
                                    @php
                                        $cargados = collect(\App\Models\Estudiante\AlumnoDocumentos::CAMPOS_OBLIGATORIOS)
                                            ->filter(fn ($c) => !empty($docs->$c))
                                            ->count();
                                        $total = count(\App\Models\Estudiante\AlumnoDocumentos::CAMPOS_OBLIGATORIOS);
                                        $estado = $docs->estado_documentos;
                                        $color  = $estado === 'APROBADO' ? 'success' : ($estado === 'PENDIENTE' ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border">
                                        {{ $estado }}
                                    </span>
                                    <small class="text-muted d-block">{{ $cargados }}/{{ $total }} docs</small>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">Sin docs</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#modalEditar{{ $uid }}">
                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i> No hay estudiantes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================
         VISTA MÓVIL / TABLET: CARDS
         ============================================================ --}}
    <div class="row g-4 d-lg-none">
        @forelse($alumnos as $alumno)
            @php
                $usuario = $alumno->usuario;
                $docs    = $alumno->documentos;
                $uid     = $alumno->alumno_id;
            @endphp
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-danger"
                                 style="width: 48px; height: 48px;">
                                {{ strtoupper(substr($usuario->name ?? 'XX', 0, 2)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0">{{ $usuario->name }}</h6>
                                <small class="text-muted d-block font-monospace">{{ $alumno->codigo_estudiante }}</small>
                                <small class="text-muted">{{ $alumno->carrera->nombre ?? '—' }}</small>
                            </div>
                        </div>
                        <div class="border rounded-3 p-3 mb-3 bg-light small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Email:</span>
                                <span class="fw-medium text-truncate ms-2" style="max-width: 60%;">{{ $usuario->email }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Docs:</span>
                                @if($docs)
                                    @php
                                        $cargados = collect(\App\Models\Estudiante\AlumnoDocumentos::CAMPOS_OBLIGATORIOS)
                                            ->filter(fn ($c) => !empty($docs->$c))
                                            ->count();
                                        $total = count(\App\Models\Estudiante\AlumnoDocumentos::CAMPOS_OBLIGATORIOS);
                                        $estado = $docs->estado_documentos;
                                        $color  = $estado === 'APROBADO' ? 'success' : ($estado === 'PENDIENTE' ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border">
                                        {{ $estado }} ({{ $cargados }}/{{ $total }})
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">Sin docs</span>
                                @endif
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                data-bs-toggle="modal" data-bs-target="#modalEditar{{ $uid }}">
                            <i class="bi bi-pencil-square me-1"></i> Editar
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted mb-0">No hay estudiantes registrados.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="mt-4 d-flex justify-content-end">
        {{ $alumnos->links() }}
    </div>
</div>

{{-- ============================================================
     MODALES (por alumno) — sin IDs duplicados
     ============================================================ --}}
@foreach($alumnos as $alumno)
    @php
        $usuario = $alumno->usuario;
        $docs    = $alumno->documentos;
        $uid     = $alumno->alumno_id;
        $prefijo = "edit-{$uid}";
    @endphp

    <div class="modal fade" id="modalEditar{{ $uid }}" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">Editar: {{ $usuario->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                <ul class="nav nav-tabs px-4 pt-3 border-bottom-0" role="tablist">
                    {{-- Tab Datos --}}
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link active px-3 py-2 fw-semibold text-secondary rounded-top border-0 bg-transparent" 
                                style="transition: all 0.2s ease-in-out;"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-datos-{{ $uid }}"
                                type="button" 
                                role="tab" 
                                aria-selected="true">
                            <i class="bi bi-person-vcard me-1"></i> Datos
                        </button>
                    </li>

                    {{-- Tab Documentos --}}
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link px-3 py-2 fw-semibold text-secondary rounded-top border-0 bg-transparent" 
                                style="transition: all 0.2s ease-in-out;"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-docs-{{ $uid }}"
                                type="button" 
                                role="tab" 
                                aria-selected="false">
                            <i class="bi bi-folder2-open me-1"></i> Documentos
                        </button>
                    </li>

                    {{-- Tab Beneficio --}}
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-2 fw-semibold text-secondary rounded-top border-0 bg-transparent position-relative" 
                                style="transition: all 0.2s ease-in-out;"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-beneficio-{{ $uid }}"
                                type="button" 
                                role="tab" 
                                aria-selected="false">
                            <i class="bi bi-cash-coin me-1"></i> Beneficio
                            @if($alumno->beneficios->isNotEmpty())
                                <span class="badge bg-success rounded-pill ms-1 align-text-top" style="font-size:0.6rem; padding: 0.25em 0.4em;">Activo</span>
                            @endif
                        </button>
                    </li>
                </ul>

                    <div class="tab-content p-4">

                        {{-- ==================== TAB DATOS ==================== --}}
                        <div class="tab-pane fade show active" id="tab-datos-{{ $uid }}">

                            <div class="alert alert-secondary small d-flex align-items-start gap-2 mb-4">
                                <i class="bi bi-info-circle-fill"></i>
                                <div>
                                    Solo se editan los <strong>datos personales</strong>. El <strong>código,
                                    correo institucional, rol y carrera</strong> no son editables desde este módulo.
                                </div>
                            </div>

                            <form action="{{ route('admin-academico.alumnos.update', $alumno) }}"
                                  method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">

                                    {{-- Nombre --}}
                                    <div class="col-12">
                                        <label for="{{ $prefijo }}-name" class="form-label small fw-medium">
                                            Nombre completo <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="name" id="{{ $prefijo }}-name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $usuario->name) }}" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Email --}}
                                    <div class="col-md-6">
                                        <label for="{{ $prefijo }}-email" class="form-label small fw-medium">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" name="email" id="{{ $prefijo }}-email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email', $usuario->email) }}" required>
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Regional --}}
                                    <div class="col-md-6">
                                        <label for="{{ $prefijo }}-regional" class="form-label small fw-medium">
                                            Regional / Sede <span class="text-danger">*</span>
                                        </label>
                                        <select name="id_regional_activo" id="{{ $prefijo }}-regional"
                                                class="form-select @error('id_regional_activo') is-invalid @enderror"
                                                required>
                                            <option value="">Seleccione...</option>
                                            @foreach($regionalactivo as $r)
                                                <option value="{{ $r->regional_activo_id }}"
                                                    {{ old('id_regional_activo', $usuario->id_regional_activo) == $r->regional_activo_id ? 'selected' : '' }}>
                                                    {{ $r->sede }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_regional_activo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Tipo doc --}}
                                    <div class="col-md-4">
                                        <label for="{{ $prefijo }}-tipodoc" class="form-label small fw-medium">
                                            Tipo de documento <span class="text-danger">*</span>
                                        </label>
                                        <select name="id_tipo_documento" id="{{ $prefijo }}-tipodoc"
                                                class="form-select tipo-doc-select @error('id_tipo_documento') is-invalid @enderror"
                                                data-doc-input="{{ $prefijo }}-doc"
                                                data-doc-hint="{{ $prefijo }}-hint"
                                                required>
                                            <option value="">Seleccione...</option>
                                            @foreach($documentosidentidad as $t)
                                                <option value="{{ $t->tipo_documento_id }}"
                                                        data-regex="{{ $t->formato_regex }}"
                                                        data-place="{{ $t->formato }}"
                                                        data-codigo="{{ $t->codigo }}"
                                                        data-nombre="{{ $t->nombre }}"
                                                    {{ old('id_tipo_documento', $usuario->id_tipo_documento) == $t->tipo_documento_id ? 'selected' : '' }}>
                                                    {{ $t->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_tipo_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Número doc --}}
                                    <div class="col-md-8">
                                        <label for="{{ $prefijo }}-doc" class="form-label small fw-medium">
                                            Número de documento <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="documento_identidad" id="{{ $prefijo }}-doc"
                                               class="form-control doc-input @error('documento_identidad') is-invalid @enderror"
                                               value="{{ old('documento_identidad', $usuario->documento_identidad) }}"
                                               required>
                                        @error('documento_identidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-text small doc-hint" id="{{ $prefijo }}-hint"></div>
                                    </div>

                                    {{-- Fecha nacimiento --}}
                                    <div class="col-md-4">
                                        <label for="{{ $prefijo }}-fnac" class="form-label small fw-medium">
                                            Fecha de nacimiento <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="fecha_nacimiento" id="{{ $prefijo }}-fnac"
                                               class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                               value="{{ old('fecha_nacimiento', optional($usuario->fecha_nacimiento)->format('Y-m-d')) }}"
                                               max="{{ now()->subYears(17)->format('Y-m-d') }}"
                                               required>
                                        @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Género --}}
                                    <div class="col-md-4">
                                        <label for="{{ $prefijo }}-genero" class="form-label small fw-medium">
                                            Género <span class="text-danger">*</span>
                                        </label>
                                        <select name="genero" id="{{ $prefijo }}-genero"
                                                class="form-select @error('genero') is-invalid @enderror"
                                                required>
                                            <option value="">Seleccione...</option>
                                            <option value="M" {{ old('genero', $usuario->genero) === 'M' ? 'selected' : '' }}>Masculino</option>
                                            <option value="F" {{ old('genero', $usuario->genero) === 'F' ? 'selected' : '' }}>Femenino</option>
                                        </select>
                                        @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Estado civil --}}
                                    <div class="col-md-4">
                                        <label for="{{ $prefijo }}-civil" class="form-label small fw-medium">Estado civil</label>
                                        <select name="estado_civil" id="{{ $prefijo }}-civil"
                                                class="form-select @error('estado_civil') is-invalid @enderror">
                                            <option value="">Seleccione...</option>
                                            @foreach(['Soltero(a)','Casado(a)','Divorciado(a)','Viudo(a)'] as $op)
                                                <option value="{{ $op }}"
                                                    {{ old('estado_civil', $usuario->estado_civil) === $op ? 'selected' : '' }}>
                                                    {{ $op }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('estado_civil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Celular --}}
                                    <div class="col-md-6">
                                        <label for="{{ $prefijo }}-cel" class="form-label small fw-medium">Celular</label>
                                        <div class="input-group">
                                            <span class="input-group-text">+503</span>
                                            <input type="text" name="celular" id="{{ $prefijo }}-cel"
                                                   class="form-control @error('celular') is-invalid @enderror"
                                                   value="{{ old('celular', $usuario->celular) }}"
                                                   placeholder="0000-0000" maxlength="9"
                                                   pattern="[0-9]{4}-[0-9]{4}">
                                        </div>
                                        @error('celular') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Dirección --}}
                                    <div class="col-md-6">
                                        <label for="{{ $prefijo }}-dir" class="form-label small fw-medium">Dirección</label>
                                        <input type="text" name="direccion" id="{{ $prefijo }}-dir"
                                               class="form-control @error('direccion') is-invalid @enderror"
                                               value="{{ old('direccion', $usuario->direccion) }}"
                                               placeholder="Ej: Col. Escalón, San Salvador">
                                        @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-check-circle me-1"></i> Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- ==================== TAB DOCUMENTOS ==================== --}}
                        <div class="tab-pane fade" id="tab-docs-{{ $uid }}">

                            @php
                                $campos = [
                                    'titulo_bachillerato' => ['Título de Bachillerato', 'bi-file-earmark-text',  'local'],
                                    'partida_nacimiento'  => ['Partida de Nacimiento',  'bi-file-earmark-text',  'local'],
                                    'fotografia_personal' => ['Fotografía Personal',    'bi-person-badge',       'public'],
                                    'constancia_paes'     => ['Constancia PAES',        'bi-file-earmark-check', 'local'],
                                ];
                            @endphp

                            <div class="row g-3 mb-4">
                                @foreach($campos as $campo => [$label, $icon, $disco])
                                    @php
                                        $ruta     = $docs->$campo ?? null;
                                        $existe   = $ruta && \Storage::disk($disco)->exists($ruta);
                                        $esImagen = $ruta && in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['jpg','jpeg','png']);
                                        $url      = $existe
                                            ? route('admin-academico.documentos.download', ['alumno' => $uid, 'campo' => $campo])
                                            : null;
                                    @endphp

                                    
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 h-100 bg-light">

                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="fw-semibold text-muted">
                                                    <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                                                </small>
                                                @if($existe)
                                                    <span class="badge bg-success-subtle text-success border small">Cargado</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border small">No cargado</span>
                                                @endif
                                            </div>

                                            @if($existe)
                                                @if($esImagen)
                                                    <a href="{{ $url }}" target="_blank">
                                                        <img src="{{ $url }}"
                                                             alt="{{ $label }}"
                                                             class="img-fluid rounded border mb-2"
                                                             style="max-height: 140px; object-fit: contain; width: 100%;">
                                                    </a>
                                                @else
                                                    <div class="text-center py-3 border rounded mb-2 bg-white">
                                                        <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                                        <div class="mt-2">
                                                            <a href="{{ $url }}" target="_blank"
                                                               class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-download me-1"></i> Ver archivo
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif

                                                @php
                                                    $noEliminables = ['fotografia_personal', 'partida_nacimiento'];
                                                @endphp

                                                @if(!in_array($campo, $noEliminables, true))
                                                    <form action="{{ route('admin-academico.alumnos.documentos.delete', [$alumno, $campo]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('¿Eliminar este documento? No se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                            <i class="bi bi-trash me-1"></i> Eliminar documento
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                                                        <i class="bi bi-lock-fill"></i>
                                                        Documento protegido: solo se puede visualizar y reemplazar.
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-center py-4 text-muted small">Sin archivo cargado</div>
                                            @endif

                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-4">

                            <h6 class="text-danger fw-bold mb-3">
                                <i class="bi bi-upload me-1"></i> Subir o reemplazar documentos
                            </h6>

                            <form action="{{ route('admin-academico.alumnos.documentos.upload', $alumno) }}"
                                  method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Título de bachillerato</label>
                                        <input type="file" name="titulo_bachillerato" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Partida de nacimiento</label>
                                        <input type="file" name="partida_nacimiento" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Fotografía personal</label>
                                        <input type="file" name="fotografia_personal" class="form-control" accept=".jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Constancia PAES</label>
                                        <input type="file" name="constancia_paes" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-medium">Observaciones</label>
                                        <textarea name="observaciones" rows="2" class="form-control"
                                                  placeholder="Notas del expediente...">{{ $docs->observaciones ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-cloud-upload me-1"></i> Subir documentos
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- ==================== TAB BENEFICIO ==================== --}}
                        <div class="tab-pane fade" id="tab-beneficio-{{ $uid }}">

                            @php
                                $beneficioActivo = $alumno->beneficioActivo();
                            @endphp

                            @if($beneficioActivo)
                                {{-- ============================================================
                                    MODO LECTURA
                                    ============================================================ --}}
                                <div class="alert alert-info d-flex align-items-start gap-2 small mb-4">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <div>
                                        Este estudiante ya tiene un beneficio activo. Para <strong>reasignar o revocar</strong>,
                                        usá la vista
                                        <a href="{{ route('admin-academico.admin-academico.beneficios.modify-benefit-student') }}"
                                        class="alert-link">Gestión de Beneficios</a>.
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 bg-light">
                                            <small class="text-muted d-block mb-1">Tipo de beneficio</small>
                                            <strong>{{ $beneficioActivo->tipo_beneficio->label() ?? $beneficioActivo->tipo_beneficio->value }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 bg-light">
                                            <small class="text-muted d-block mb-1">Ciclo lectivo</small>
                                            <strong>{{ $beneficioActivo->cicloLectivo->nombre_ciclo ?? '—' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 bg-light">
                                            <small class="text-muted d-block mb-1">Convenio / resolución</small>
                                            <strong>{{ $beneficioActivo->nombre_convenio }}</strong>
                                            @if($beneficioActivo->resolucion_academica)
                                                <div class="small text-muted">RA: {{ $beneficioActivo->resolucion_academica }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 bg-light">
                                            <small class="text-muted d-block mb-1">Estado</small>
                                            <span class="badge bg-success-subtle text-success border">
                                                {{ $beneficioActivo->estado->label() }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($beneficioActivo->porcentaje_estudiante !== null)
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 bg-light">
                                                <small class="text-muted d-block mb-1">% Estudiante</small>
                                                <strong>{{ number_format((float) $beneficioActivo->porcentaje_estudiante, 2) }}%</strong>
                                            </div>
                                        </div>
                                    @endif
                                    @if($beneficioActivo->porcentaje_universidad !== null)
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 bg-light">
                                                <small class="text-muted d-block mb-1">% Universidad</small>
                                                <strong>{{ number_format((float) $beneficioActivo->porcentaje_universidad, 2) }}%</strong>
                                            </div>
                                        </div>
                                    @endif
                                    @if($beneficioActivo->monto_fijo_cuota !== null)
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 bg-light">
                                                <small class="text-muted d-block mb-1">Monto fijo de cuota</small>
                                                <strong>${{ number_format((float) $beneficioActivo->monto_fijo_cuota, 2) }}</strong>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <div class="border rounded-3 p-3 bg-light">
                                            <small class="text-muted d-block mb-2">Inclusiones en exoneración de pagos</small>
                                            <div class="d-flex flex-wrap gap-3 small">
                                                <span class="{{ $beneficioActivo->incluye_matricula ? 'text-success' : 'text-muted' }}">
                                                    <i class="bi bi-{{ $beneficioActivo->incluye_matricula ? 'check-circle-fill' : 'x-circle' }}"></i>
                                                    Matrícula
                                                </span>
                                                <span class="{{ $beneficioActivo->incluye_laboratorio ? 'text-success' : 'text-muted' }}">
                                                    <i class="bi bi-{{ $beneficioActivo->incluye_laboratorio ? 'check-circle-fill' : 'x-circle' }}"></i>
                                                    Laboratorio
                                                </span>
                                                <span class="{{ $beneficioActivo->incluye_derechos_grado ? 'text-success' : 'text-muted' }}">
                                                    <i class="bi bi-{{ $beneficioActivo->incluye_derechos_grado ? 'check-circle-fill' : 'x-circle' }}"></i>
                                                    Derechos de grado
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @else
                                {{-- ============================================================
                                    MODO ASIGNACIÓN
                                    ============================================================ --}}
                                <div class="alert alert-warning d-flex align-items-start gap-2 small mb-4">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <div>
                                        Este estudiante <strong>no tiene beneficio activo</strong>. Podés asignarle uno nuevo.
                                    </div>
                                </div>

                                <form action="{{ route('admin-academico.alumnos.beneficios.assign', $alumno) }}" method="POST" class="form-beneficio-asignar">
                                    @csrf
                                    <div class="row g-3">

                                        {{-- Ciclo lectivo --}}
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium">
                                                Ciclo lectivo <span class="text-danger">*</span>
                                            </label>
                                            <select name="id_ciclo_lectivo"
                                                    class="form-select form-select-sm @error('id_ciclo_lectivo') is-invalid @enderror"
                                                    required>
                                                <option value="">Seleccione...</option>
                                                @foreach($ciclosLectivos as $ciclo)
                                                    <option value="{{ $ciclo->ciclo_lectivo_id }}"
                                                        {{ old('id_ciclo_lectivo') == $ciclo->ciclo_lectivo_id ? 'selected' : '' }}>
                                                        {{ $ciclo->nombre_ciclo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('id_ciclo_lectivo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Tipo de beneficio --}}
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium">
                                                Tipo de beneficio <span class="text-danger">*</span>
                                            </label>
                                            <select name="tipo_beneficio"
                                                    class="form-select form-select-sm @error('tipo_beneficio') is-invalid @enderror"
                                                    required>
                                                <option value="">Seleccione...</option>
                                                @foreach(\App\Enums\TipoBeneficio::cases() as $tb)
                                                    <option value="{{ $tb->value }}"
                                                        {{ old('tipo_beneficio') === $tb->value ? 'selected' : '' }}>
                                                        {{ $tb->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tipo_beneficio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Convenio --}}
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium">
                                                Nombre del convenio / beca <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="nombre_convenio"
                                                class="form-control form-control-sm @error('nombre_convenio') is-invalid @enderror"
                                                value="{{ old('nombre_convenio') }}"
                                                placeholder="Ej: Convenio UMA-FUSAL" required>
                                            @error('nombre_convenio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Resolución --}}
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium">Resolución académica</label>
                                            <input type="text" name="resolucion_academica"
                                                class="form-control form-control-sm @error('resolucion_academica') is-invalid @enderror"
                                                value="{{ old('resolucion_academica') }}"
                                                placeholder="Ej: RA-2026-014">
                                            @error('resolucion_academica')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Porcentajes (solo BECA_PARCIAL / CUOTA_ESPECIAL) --}}
                                        <div class="col-12 campo-pct-beneficio d-none">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-medium">% Estudiante</label>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        name="porcentaje_estudiante"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('porcentaje_estudiante') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-medium">% Universidad</label>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        name="porcentaje_universidad"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('porcentaje_universidad') }}">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Monto fijo (solo FRANJA_BECARIA) --}}
                                        <div class="col-md-6 campo-monto-beneficio d-none">
                                            <label class="form-label small fw-medium">Monto fijo de cuota ($)</label>
                                            <input type="number" step="0.01" min="0"
                                                name="monto_fijo_cuota"
                                                class="form-control form-control-sm"
                                                value="{{ old('monto_fijo_cuota') }}"
                                                placeholder="Ej: 53.00">
                                        </div>

                                        {{-- Matrícula (BECA_PARCIAL / CUOTA_ESPECIAL) --}}
                                        <div class="col-md-6 campo-matricula-beneficio d-none">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox"
                                                    name="incluye_matricula" value="1"
                                                    id="{{ $prefijo }}-matricula"
                                                    {{ old('incluye_matricula') ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="{{ $prefijo }}-matricula">
                                                    Incluir <strong>matrícula</strong> en la cobertura
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Laboratorio (BECA_COMPLETA) --}}
                                        <div class="col-md-6 campo-laboratorio-beneficio d-none">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox"
                                                    name="incluye_laboratorio" value="1"
                                                    id="{{ $prefijo }}-lab"
                                                    {{ old('incluye_laboratorio') ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="{{ $prefijo }}-lab">
                                                    Incluir <strong>laboratorio</strong> en la cobertura
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-check-circle me-1"></i> Asignar beneficio
                                        </button>
                                    </div>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Aplicar pattern/placeholder dinámicos y máscara mínima por modal
    document.querySelectorAll('.tipo-doc-select').forEach(select => {
        const inputId = select.dataset.docInput;
        const hintId  = select.dataset.docHint;
        const input   = document.getElementById(inputId);
        const hint    = hintId ? document.getElementById(hintId) : null;
        if (!input) return;

        const actualizar = () => {
            const opt = select.options[select.selectedIndex];
            const regex = opt?.dataset.regex;
            const place = opt?.dataset.place;

            if (regex) {
                input.setAttribute('pattern', regex.replace(/\\\\/g, '\\'));
                input.setAttribute('placeholder', place || '');
                if (hint) hint.textContent = `Formato: ${place || ''}`;
            } else {
                input.removeAttribute('pattern');
                input.removeAttribute('placeholder');
                if (hint) hint.textContent = '';
            }
        };

        // Máscara simple para celular
        const celInput = document.getElementById(inputId.replace('-doc', '-cel'));
        if (celInput) {
            celInput.addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 4) v = v.substring(0, 4) + '-' + v.substring(4, 8);
                e.target.value = v;
            });
        }

        select.addEventListener('change', actualizar);
        actualizar();
    });

    /* ============================================================
        3. TAB BENEFICIO: mostrar/ocultar campos según tipo
    ============================================================ */
    document.querySelectorAll('.form-beneficio-asignar').forEach(form => {

        const selectTipo   = form.querySelector('select[name="tipo_beneficio"]');
        
        if (!selectTipo) return;

        const bloquePct       = form.querySelectorAll('.campo-pct-beneficio');
        const bloqueMonto     = form.querySelectorAll('.campo-monto-beneficio');
        const bloqueMatricula = form.querySelectorAll('.campo-matricula-beneficio');
        const bloqueLab       = form.querySelectorAll('.campo-laboratorio-beneficio');

        const pctEst  = form.querySelector('input[name="porcentaje_estudiante"]');
        const pctUniv = form.querySelector('input[name="porcentaje_universidad"]');
        const monto   = form.querySelector('input[name="monto_fijo_cuota"]');

        const chkMatricula = form.querySelector('input[name="incluye_matricula"]');
        const chkLab       = form.querySelector('input[name="incluye_laboratorio"]');

        function actualizar() {
            const tipo = selectTipo.value;

            const esParcial  = tipo === 'BECA_PARCIAL' || tipo === 'CUOTA_ESPECIAL';
            const esFranja   = tipo === 'FRANJA_BECARIA';
            const esCompleta = tipo === 'BECA_COMPLETA';

            // Mostrar / ocultar
            bloquePct.forEach(el       => el.classList.toggle('d-none', !esParcial));
            bloqueMonto.forEach(el     => el.classList.toggle('d-none', !esFranja));
            bloqueMatricula.forEach(el => el.classList.toggle('d-none', !esParcial));
            bloqueLab.forEach(el       => el.classList.toggle('d-none', !esCompleta));

            // Valores sugeridos
            if (esCompleta) {
                if (pctEst)  pctEst.value  = '0';
                if (pctUniv) pctUniv.value = '100';
            } else if (tipo === 'CUOTA_ESPECIAL') {
                if (pctEst && !pctEst.value)   pctEst.value  = '88.33';
                if (pctUniv && !pctUniv.value) pctUniv.value = '11.67';
            } else if (tipo === 'BECA_PARCIAL') {
                if (pctEst && !pctEst.value)   pctEst.value  = '50';
                if (pctUniv && !pctUniv.value) pctUniv.value = '50';
            } else if (esFranja) {
                if (monto && !monto.value) monto.value = '53.00';
            }

            // Desmarcar checkboxes al ocultarlos (para no enviar valores residuales)
            if (!esParcial  && chkMatricula) chkMatricula.checked = false;
            if (!esCompleta && chkLab)       chkLab.checked       = false;
        }

        // Sincroniza % Universidad cuando cambia % Estudiante
        pctEst?.addEventListener('input', () => {
            if (pctUniv && pctEst.value !== '') {
                const est = parseFloat(pctEst.value || 0);
                pctUniv.value = (100 - est).toFixed(2);
            }
        });

        selectTipo.addEventListener('change', actualizar);
        actualizar(); // inicializar
    });
});
</script>
@endpush