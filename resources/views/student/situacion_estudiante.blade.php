@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-chat-square-text-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">Buzón de Situaciones</h2>
                <p class="text-muted mb-0 small">
                    Reporta problemas académicos, financieros o administrativos.
                </p>
            </div>
        </div>
        <span class="badge bg-danger">
            {{ $situaciones->total() }} situación(es) activa(s)
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

    <div class="row g-4">

        {{-- FORMULARIO DE NUEVA SITUACIÓN --}}
        <div class="col-lg-5">
            <form method="POST"
                  action="{{ route('student.situaciones.store') }}"
                  class="card border-0 shadow-sm p-4"
                  novalidate>
                @csrf

                <h6 class="fw-bold text-uppercase small text-muted mb-3">
                    <i class="bi bi-plus-circle me-1"></i> Nueva situación
                </h6>

                <div class="alert alert-light border small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Describe tu situación con el mayor detalle posible.
                </div>

                <div class="mb-3">
                    <label for="tipo_situacion" class="form-label fw-semibold small">
                        Tipo de situación <span class="text-danger">*</span>
                    </label>
                    <select name="tipo_situacion"
                            id="tipo_situacion"
                            class="form-select @error('tipo_situacion') is-invalid @enderror"
                            required>
                        <option value="">Selecciona un tipo</option>
                        @foreach($tipos as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('tipo_situacion') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_situacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="nombre_usuario" class="form-label fw-semibold small">
                            Nombre completo <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nombre_usuario"
                               id="nombre_usuario"
                               class="form-control @error('nombre_usuario') is-invalid @enderror"
                               value="{{ old('nombre_usuario', $prefill['nombre_usuario']) }}"
                               maxlength="150"
                               required>
                        @error('nombre_usuario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="carnet_institucional" class="form-label fw-semibold small">
                            Carnet institucional <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="carnet_institucional"
                               id="carnet_institucional"
                               class="form-control @error('carnet_institucional') is-invalid @enderror"
                               value="{{ old('carnet_institucional', $prefill['carnet_institucional']) }}"
                               maxlength="25"
                               pattern="[A-Za-z0-9-]+"
                               required>
                        @error('carnet_institucional')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="correo" class="form-label fw-semibold small">
                            Correo electrónico <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               name="correo"
                               id="correo"
                               class="form-control @error('correo') is-invalid @enderror"
                               value="{{ old('correo', $prefill['correo']) }}"
                               maxlength="255"
                               required>
                        @error('correo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3 mb-3">
                    <label for="detalle" class="form-label fw-semibold small">
                        Descripción <span class="text-danger">*</span>
                    </label>
                    <textarea name="detalle"
                              id="detalle"
                              rows="5"
                              maxlength="2000"
                              class="form-control @error('detalle') is-invalid @enderror"
                              placeholder="Explica qué sucede y qué esperas que se resuelva..."
                              required>{{ old('detalle') }}</textarea>
                    <div class="d-flex justify-content-between mt-1">
                        @error('detalle')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @else
                            <small class="text-muted">Mínimo 10 caracteres.</small>
                        @enderror
                        <small class="text-muted"><span id="contadorDetalle">0</span>/2000</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-send-fill me-1"></i> Enviar situación
                </button>
            </form>
        </div>

        {{-- HISTORIAL --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-uppercase small text-muted">
                        <i class="bi bi-clock-history me-1"></i> Historial de situaciones
                    </h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Detalle</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($situaciones as $s)
                                <tr>
                                    <td>
                                        <span class="badge {{ $s->tipo_situacion->badge() }}">
                                            {{ $s->tipo_situacion->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $s->estado->badge() }}">
                                            {{ $s->estado->label() }}
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 220px;">
                                        {{ \Illuminate\Support\Str::limit($s->detalle, 50) }}
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $s->creado_en->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetalleSituacion"
                                                    data-tipo-label="{{ $s->tipo_situacion->label() }}"
                                                    data-tipo-badge="{{ $s->tipo_situacion->badge() }}"
                                                    data-estado-label="{{ $s->estado->label() }}"
                                                    data-estado-badge="{{ $s->estado->badge() }}"
                                                    data-nombre="{{ $s->nombre_usuario }}"
                                                    data-carnet="{{ $s->carnet_institucional }}"
                                                    data-correo="{{ $s->correo }}"
                                                    data-detalle="{{ $s->detalle }}"
                                                    data-fecha="{{ $s->creado_en->format('d/m/Y H:i') }}">
                                                <i class="bi bi-eye"></i> Ver
                                            </button>

                                            @if($s->esEliminable())
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEliminarSituacion"
                                                        data-id="{{ $s->id }}"
                                                        data-tipo="{{ $s->tipo_situacion->label() }}"
                                                        data-delete-url="{{ route('student.situaciones.destroy', $s->id) }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Aún no has registrado ninguna situación.
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
    </div>
</div>

{{-- MODAL DE DETALLE --}}
<div class="modal fade" id="modalDetalleSituacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-file-text-fill me-1"></i> Detalle de la situación
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

                <h6 class="fw-bold text-uppercase small text-muted mb-2">Descripción</h6>
                <p id="mDetalle" class="mb-0" style="white-space: pre-wrap;"></p>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE ELIMINACIÓN --}}
<div class="modal fade" id="modalEliminarSituacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST"
              id="formEliminarSituacion"
              class="modal-content border-0 shadow">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Eliminar situación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <p class="mb-2">
                    ¿Estás seguro de eliminar esta situación?
                </p>
                <p class="mb-2 small text-muted">
                    Tipo: <strong id="mEliminarTipo"></strong>
                </p>
                <div class="alert alert-warning border-0 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Esta acción no se puede deshacer. Solo puedes eliminar situaciones en estado <strong>PENDIENTE</strong>.
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash-fill me-1"></i> Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* Contador de caracteres */
    const detalle  = document.getElementById('detalle');
    const contador = document.getElementById('contadorDetalle');

    if (detalle && contador) {
        const update = () => contador.textContent = detalle.value.length;
        detalle.addEventListener('input', update);
        update();
    }

    /* Modal de detalle */
    const modalDetalle = document.getElementById('modalDetalleSituacion');

    if (modalDetalle) {
        modalDetalle.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            document.getElementById('mTipoBadge').className   = 'badge ' + btn.getAttribute('data-tipo-badge');
            document.getElementById('mTipoBadge').textContent = btn.getAttribute('data-tipo-label');

            document.getElementById('mEstadoBadge').className   = 'badge ' + btn.getAttribute('data-estado-badge');
            document.getElementById('mEstadoBadge').textContent = btn.getAttribute('data-estado-label');

            document.getElementById('mNombre').textContent  = btn.getAttribute('data-nombre');
            document.getElementById('mCarnet').textContent  = btn.getAttribute('data-carnet');
            document.getElementById('mCorreo').textContent  = btn.getAttribute('data-correo');
            document.getElementById('mDetalle').textContent = btn.getAttribute('data-detalle');
            document.getElementById('mFecha').textContent   = btn.getAttribute('data-fecha');
        });
    }

    /* Modal de eliminación */
    const modalEliminar = document.getElementById('modalEliminarSituacion');
    const formEliminar  = document.getElementById('formEliminarSituacion');

    if (modalEliminar && formEliminar) {
        modalEliminar.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            formEliminar.action = btn.getAttribute('data-delete-url');
            document.getElementById('mEliminarTipo').textContent = btn.getAttribute('data-tipo');
        });
    }
});
</script>
@endpush
@endsection