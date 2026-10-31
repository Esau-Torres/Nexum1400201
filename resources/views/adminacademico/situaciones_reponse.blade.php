@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-inboxes-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">Gestión de Situaciones</h2>
                <p class="text-muted mb-0 small">
                    Buzón de observaciones reportadas por los estudiantes.
                </p>
            </div>
        </div>
        <span class="badge bg-danger">
            {{ $situaciones->total() }} situación(es)
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="bi bi-x-circle-fill me-1"></i>{{ session('error') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin-academico.situaciones.gestion') }}"
          class="card border-0 shadow-sm p-3 mb-4">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="search" name="buscar" class="form-control"
                       placeholder="Buscar por nombre, carnet, correo o detalle..."
                       value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $value => $label)
                        <option value="{{ $value }}" {{ request('estado') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $value => $label)
                        <option value="{{ $value }}" {{ request('tipo') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Estado</th>
                        <th>Tipo</th>
                        <th>Alumno</th>
                        <th>Carnet</th>
                        <th>Detalle</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($situaciones as $s)
                        <tr>
                            <td>
                                <span class="badge {{ $s->estado->badge() }}">
                                    {{ $s->estado->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $s->tipo_situacion->badge() }}">
                                    {{ $s->tipo_situacion->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $s->nombre_usuario }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    {{ $s->correo }}
                                </div>
                            </td>
                            <td>
                                <code class="small">{{ $s->carnet_institucional }}</code>
                            </td>
                            <td class="text-truncate" style="max-width: 240px;">
                                {{ \Illuminate\Support\Str::limit($s->detalle, 50) }}
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $s->creado_en->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalle"
                                        data-id="{{ $s->id }}"
                                        data-tipo-label="{{ $s->tipo_situacion->label() }}"
                                        data-tipo-badge="{{ $s->tipo_situacion->badge() }}"
                                        data-estado-label="{{ $s->estado->label() }}"
                                        data-estado-badge="{{ $s->estado->badge() }}"
                                        data-estado-value="{{ $s->estado->value }}"
                                        data-nombre="{{ $s->nombre_usuario }}"
                                        data-carnet="{{ $s->carnet_institucional }}"
                                        data-correo="{{ $s->correo }}"
                                        data-detalle="{{ $s->detalle }}"
                                        data-fecha="{{ $s->creado_en->format('d/m/Y H:i') }}"
                                        data-estado-url="{{ route('admin-academico.situaciones.gestion.cambiar-estado', $s->id) }}">
                                    <i class="bi bi-eye"></i> Ver / Gestionar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay situaciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($situaciones->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $situaciones->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-file-earmark-text-fill me-1"></i> Detalle de la situación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge me-1" id="mTipoBadge"></span>
                        <span class="badge" id="mEstadoBadge"></span>
                        <h6 class="fw-bold mt-2 mb-1" id="mNombre"></h6>
                        <small class="text-muted d-block">
                            <i class="bi bi-person-vcard me-1"></i><span id="mCarnet"></span>
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-envelope me-1"></i><span id="mCorreo"></span>
                        </small>
                    </div>
                    <small class="text-muted text-end">
                        <i class="bi bi-clock me-1"></i><span id="mFecha"></span>
                    </small>
                </div>

                <hr>

                <h6 class="fw-bold text-uppercase small text-muted mb-2">Descripción del alumno</h6>
                <p id="mDetalle" class="mb-3" style="white-space: pre-wrap;"></p>

                <hr>

                <h6 class="fw-bold text-uppercase small text-muted mb-2">Contactar al alumno y gestionar</h6>
                <p class="small text-muted mb-3">
                    Redacta un correo al estudiante con la solución y, si corresponde, actualiza el estado.
                </p>

                {{-- Fila: botones de correo + selector de estado + botón guardar --}}
                <div class="row g-3 align-items-end mb-2">

                    {{-- Botones de contacto --}}
                    <div class="col-md-5">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="#"
                            id="btnGmail"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-google me-1"></i> Redactar en Gmail
                            </a>
                            <a href="#"
                            id="btnMailto"
                            class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-envelope me-1"></i> Cliente de correo
                            </a>
                        </div>
                    </div>

                    {{-- Selector de estado + botón guardar --}}
                    <div class="col-md-7" id="wrapperCambioEstado">
                        <form method="POST" id="formCambioEstado" novalidate>
                            @csrf
                            @method('PATCH')

                            <div class="row g-2 align-items-end">
                                <div class="col-8">
                                    <label for="selectEstado" class="form-label small fw-semibold mb-1">
                                        Estado <span class="text-danger">*</span>
                                    </label>
                                    <select name="estado" id="selectEstado" class="form-select form-select-sm" required>
                                        <option value="">Selecciona...</option>
                                        @foreach($estadosDestino as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-4">
                                    <button type="submit" class="btn btn-danger btn-sm w-100" id="btnGuardarEstado">
                                        <i class="bi bi-check2-circle me-1"></i> Guardar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Aviso si ya está cerrado --}}
                <div id="avisoCerrado" class="alert alert-success border-0 small mt-3 d-none">
                    <i class="bi bi-lock-fill me-1"></i>
                    Esta situación está <strong>cerrada</strong> y no admite más cambios de estado.
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal     = document.getElementById('modalDetalle');
    const form      = document.getElementById('formCambioEstado');
    const select    = document.getElementById('selectEstado');
    const btnGuardar= document.getElementById('btnGuardarEstado');
    const wrapper   = document.getElementById('wrapperCambioEstado');
    const aviso     = document.getElementById('avisoCerrado');
    const btnGmail  = document.getElementById('btnGmail');
    const btnMailto = document.getElementById('btnMailto');

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;

        const nombre      = btn.getAttribute('data-nombre');
        const carnet      = btn.getAttribute('data-carnet');
        const correo      = btn.getAttribute('data-correo');
        const detalle     = btn.getAttribute('data-detalle');
        const tipoLabel   = btn.getAttribute('data-tipo-label');
        const tipoBadge   = btn.getAttribute('data-tipo-badge');
        const estadoLabel = btn.getAttribute('data-estado-label');
        const estadoBadge = btn.getAttribute('data-estado-badge');
        const estadoValue = btn.getAttribute('data-estado-value');
        const fecha       = btn.getAttribute('data-fecha');
        const estadoUrl   = btn.getAttribute('data-estado-url');

        document.getElementById('mTipoBadge').className   = 'badge ' + tipoBadge;
        document.getElementById('mTipoBadge').textContent = tipoLabel;

        document.getElementById('mEstadoBadge').className   = 'badge ' + estadoBadge;
        document.getElementById('mEstadoBadge').textContent = estadoLabel;

        document.getElementById('mNombre').textContent  = nombre;
        document.getElementById('mCarnet').textContent  = carnet;
        document.getElementById('mCorreo').textContent  = correo;
        document.getElementById('mDetalle').textContent = detalle;
        document.getElementById('mFecha').textContent   = fecha;

        const asunto = `Situación ${tipoLabel} - Carnet ${carnet}`;
        const cuerpo =
            `Hola ${nombre},\n\n` +
            `Recibimos tu situación de tipo "${tipoLabel}".\n\n` +
            `Detalle reportado:\n"${detalle}"\n\n` +
            `Respuesta:\n[Escribe aquí la solución o seguimiento]\n\n` +
            `Atentamente,\nAdministración Académica`;

        btnGmail.href =
            'https://mail.google.com/mail/?view=cm&fs=1' +
            '&to='   + encodeURIComponent(correo) +
            '&su='   + encodeURIComponent(asunto) +
            '&body=' + encodeURIComponent(cuerpo);

        btnMailto.href =
            'mailto:' + encodeURIComponent(correo) +
            '?subject=' + encodeURIComponent(asunto) +
            '&body='    + encodeURIComponent(cuerpo);

        form.action = estadoUrl;

        select.value = '';

        if (estadoValue === 'CERRADO') {
            wrapper.classList.add('d-none');
            aviso.classList.remove('d-none');
        } else {
            wrapper.classList.remove('d-none');
            aviso.classList.add('d-none');
        }
    });

    form.addEventListener('submit', function (e) {
        if (!select.value) {
            e.preventDefault();
            select.focus();
            return;
        }
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
    });
});
</script>
@endpush
@endsection