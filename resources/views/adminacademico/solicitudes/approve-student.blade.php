@extends('layouts.app')

@section('title', 'Solicitudes de Inscripción')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width: 48px; height: 48px;">
                <i class="bi bi-inbox-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">Solicitudes de Inscripción</h1>
                <p class="text-muted mb-0 small">
                    Estudiantes registrados en línea pendientes de revisión
                </p>
            </div>
        </div>
        <div>
            <a href="{{ route('admin-academico.modify-student') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
                <i class="fa-solid fa-user me-1"></i> gestionar alumnos
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Tabla --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div>
                <table class="table table-hover align-middle mb-0" id="tabla-solicitudes">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nombre</th>
                            <th>Email</th>
                            <th>Celular</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes as $sol)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-danger"
                                             style="width: 38px; height: 38px; font-size: 0.85rem;">
                                            {{ strtoupper(substr($sol->usuario->name ?? 'XX', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $sol->usuario->name ?? '—' }}</div>
                                            <small class="text-muted">
                                                Código: {{ $sol->codigo_estudiante }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $sol->usuario->email ?? '—' }}</div>
                                    <small class="text-muted">{{ $sol->correo_institucional }}</small>
                                </td>
                                <td>{{ $sol->usuario->celular ?? '—' }}</td>
                                <td>
                                    @if($sol->usuario->estado === 0)
                                        <span class="badge bg-warning-subtle text-warning border border-warning">
                                            <i class="bi bi-hourglass-split me-1"></i>Pendiente
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>Activo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalSolicitud"
                                            data-url="{{ route('admin-academico.admin.academico.solicitudes.show', $sol) }}">
                                        <i class="bi bi-eye me-1"></i> Ver detalle
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No hay solicitudes pendientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-3 d-flex justify-content-end">
        {{ $solicitudes->links() }}
    </div>

</div>

{{-- Modal contenedor --}}
<div class="modal fade" id="modalSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">Detalle de la solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" id="modalSolicitudBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="text-muted mt-3 mb-0">Cargando solicitud...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal      = document.getElementById('modalSolicitud');
    const modalBody  = document.getElementById('modalSolicitudBody');

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const url = trigger.getAttribute('data-url');

        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-danger" role="status"></div>
                <p class="text-muted mt-3 mb-0">Cargando solicitud...</p>
            </div>`;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                modalBody.innerHTML = html;

                modalBody.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    newScript.textContent = oldScript.textContent;
                    document.body.appendChild(newScript).parentNode.removeChild(newScript);
                });
            })
            .catch(() => {
                modalBody.innerHTML = `
                    <div class="alert alert-danger m-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        No se pudo cargar la solicitud. Intentá nuevamente.
                    </div>`;
            });
    });

    modal.addEventListener('hidden.bs.modal', function () {
        modalBody.innerHTML = '';
    });
});
</script>
@endpush