@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <!-- Encabezado y botón de Crear -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Gestión de aranceles</h2>
            <p class="text-muted">Administración de aranceles</p>
        </div>
        <div>
            <button type="button" class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle me-1"></i> Nuevo arancel
            </button>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="card shadow-sm border-0">

        <!-- Buscador y Filtro (Diseño Reparado y Responsivo) -->
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('adfinanciero.conceptospagos.index') }}" class="m-0">
                <div class="row align-items-center g-3">

                    <!-- Título a la izquierda -->
                    <div class="col-12 col-lg-4">
                        <h5 class="mb-0">Listado de aranceles</h5>
                    </div>

                    <!-- Filtros a la derecha -->
                    <div class="col-12 col-lg-8">
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-lg-end">

                            <!-- Select de Categoría -->
                            <select name="categoria" class="form-select w-100" style="max-width: 250px;" onchange="this.form.submit()">
                                <option value="">Todas las categorías</option>
                                @foreach($categoriasUnicas as $cat)
                                    <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Input de Búsqueda -->
                            <div class="input-group w-100" style="max-width: 350px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Buscar concepto o código..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-outline-secondary bg-light text-dark">Buscar</button>
                            </div>

                        </div>
                    </div>

                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <!-- Contenedor flex con min-height para evitar deformaciones -->
            <div class="table-responsive d-flex flex-column justify-content-between" style="min-height: 600px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Monto Base</th>
                            <th class="text-center">Dcto. Estudiante</th>
                            <th class="text-center">Recurrente</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conceptos as $concepto)
                        <!-- Fila con borde sutil -->
                        <tr class="border-bottom">
                            <!-- py-3 le da el "aire" vertical que necesitamos -->
                            <td class="py-3">
                                <span class="badge bg-secondary text-white">{{ $concepto->codigo_concepto }}</span>
                            </td>

                            <!-- Le ponemos un max-width para que el texto largo se acomode con elegancia -->
                            <td class="py-3 fw-medium text-uppercase text-dark" style="font-size: 0.9rem; max-width: 350px;">
                                {{ $concepto->nombre }}
                            </td>

                            <!-- Hacemos la categoría text-muted para que pase a un segundo plano visual -->
                            <td class="py-3 text-uppercase text-muted" style="font-size: 0.85rem;">
                                <i class="bi bi-folder2 me-1"></i>{{ $concepto->categoria }}
                            </td>

                            <!-- Destacamos un poquito el precio -->
                            <td class="py-3 fw-bold" style="color: #4a4a4a; font-size: 0.95rem;">
                                $ {{ number_format($concepto->monto_base, 2) }}
                            </td>

                            <td class="py-3 text-center">
                                @if($concepto->aplica_descuento_estudiante)
                                    <span class="badge px-2 py-1" style="background-color: #f4f9fd; color: #5dade2; border: 1px solid #5dade2;">Sí</span>
                                @else
                                    <span class="badge px-2 py-1" style="background-color: #fff5f5; color: #ff7575; border: 1px solid #ff7575;">No</span>
                                @endif
                            </td>

                            <td class="py-3 text-center">
                                @if($concepto->es_recurrente)
                                    <span class="badge px-2 py-1" style="background-color: #f4f9fd; color: #5dade2; border: 1px solid #5dade2;">Sí</span>
                                @else
                                    <span class="badge px-2 py-1" style="background-color: #fff5f5; color: #ff7575; border: 1px solid #ff7575;">No</span>
                                @endif
                            </td>

                            <td class="py-3 text-center">
                                @if($concepto->estado)
                                    <!-- Estado Activo -->
                                    <span class="badge px-3 py-1 shadow-sm" style="background-color: #121212; color: #f0f2f5;">
                                        Activo
                                    </span>
                                @else
                                    <!-- Estado Inactivo -->
                                    <span class="badge px-3 py-1" style="background-color: #fff5f5; color: #dc3545; border: 1px solid #ff7575;">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $concepto->concepto_pago_id }}" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- Modal de Edición (Debe ir dentro del forelse) -->
                        <div class="modal fade" id="editModal{{ $concepto->concepto_pago_id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">

                                    <!-- Cabecera Minimalista (Fondo blanco, texto oscuro, icono de color) -->
                                    <div class="modal-header border-bottom-0 pb-2">
                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                                            <i class="bi bi-pencil-fill me-2" style="color: #5dade2;;"></i>
                                            Editar arancel
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <form action="{{ route('adfinanciero.conceptospagos.update', $concepto->concepto_pago_id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body pt-0">

                                            <!-- Caja de información (Diseño basado en tu imagen) -->
                                            <div class="mb-4 p-3 bg-light rounded" style="border-left: 4px solid #ff7575;">
                                                <i class="bi bi-info-circle-fill me-2" style="color: #5dade2;;"></i>
                                                <span class="text-secondary" style="font-size: 0.9rem;">Modifique los valores necesarios.</span>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label text-dark">Código del concepto</label>
                                                    <input type="text" class="form-control" name="codigo_concepto" value="{{ $concepto->codigo_concepto }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-dark">Categoría</label>
                                                    <input type="text" class="form-control text-uppercase" name="categoria" value="{{ $concepto->categoria }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-dark">Nombre del arancel</label>
                                                    <input type="text" class="form-control text-uppercase" name="nombre" value="{{ $concepto->nombre }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-dark">Monto base ($)</label>
                                                    <input type="number" step="0.01" class="form-control" name="monto_base" value="{{ $concepto->monto_base }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-dark">Dcto. Estudiante</label>
                                                    <select class="form-select" name="aplica_descuento_estudiante">
                                                        <option value="1" {{ $concepto->aplica_descuento_estudiante ? 'selected' : '' }}>Sí, aplica</option>
                                                        <option value="0" {{ !$concepto->aplica_descuento_estudiante ? 'selected' : '' }}>No aplica</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-dark">¿Es recurrente?</label>
                                                    <select class="form-select" name="es_recurrente">
                                                        <option value="1" {{ $concepto->es_recurrente ? 'selected' : '' }}>Sí</option>
                                                        <option value="0" {{ !$concepto->es_recurrente ? 'selected' : '' }}>No</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-dark">Estado</label>
                                                    <select class="form-select" name="estado">
                                                        <option value="1" {{ $concepto->estado ? 'selected' : '' }}>Activo</option>
                                                        <option value="0" {{ !$concepto->estado ? 'selected' : '' }}>Inactivo</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer sin borde superior visible, botón cancelar delineado -->
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn text-white px-4" style="background-color: #dc3545;">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No se encontraron aranceles con los filtros actuales.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Paginación Personalizada (Sin inglés y de 3 números) -->
                <div class="mt-auto p-3 border-top bg-white d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando resultados de la base de datos.</small>

                    @if ($conceptos->hasPages())
                        <ul class="pagination mb-0 shadow-sm">
                            {{-- Botón Anterior --}}
                            <li class="page-item {{ $conceptos->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $conceptos->previousPageUrl() }}" {!! $conceptos->onFirstPage() ? 'tabindex="-1" aria-disabled="true"' : '' !!}>&lsaquo;</a>
                            </li>

                            {{-- Lógica para mostrar siempre un máximo de 3 números --}}
                            @php
                                $start = max(1, $conceptos->currentPage() - 1);
                                $end = min($conceptos->lastPage(), $start + 2);
                                if ($end - $start < 2 && $start > 1) {
                                    $start = max(1, $end - 2);
                                }
                            @endphp

                            @foreach ($conceptos->getUrlRange($start, $end) as $page => $url)
                                <li class="page-item {{ $page == $conceptos->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            {{-- Botón Siguiente --}}
                            <li class="page-item {{ !$conceptos->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $conceptos->nextPageUrl() }}" {!! !$conceptos->hasMorePages() ? 'tabindex="-1" aria-disabled="true"' : '' !!}>&rsaquo;</a>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- Modal de Creación (FUERA DE LA TABLA)      -->
<!-- ========================================== -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <!-- Cabecera Minimalista -->
            <div class="modal-header border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-tag-fill me-2" style="color: #5dade2;"></i>
                    Registrar nuevo arancel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('adfinanciero.conceptospagos.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-0">

                    <!-- Caja de información (Diseño basado en tu imagen) -->
                    <div class="mb-4 p-3 bg-light rounded" style="border-left: 4px solid #ff7575;">
                        <i class="bi bi-info-circle-fill me-2" style="color: #5dade2;"></i>
                        <span class="text-secondary" style="font-size: 0.9rem;">Al confirmar, se guardará el arancel en el catálogo. Asegúrese de que el código no esté repetido.</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark">Código del concepto</label>
                            <input type="text" class="form-control" name="codigo_concepto" placeholder="Ej. MAT-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark">Categoría</label>
                            <input type="text" class="form-control text-uppercase" name="categoria" placeholder="Ej. Matrícula" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-dark">Nombre del arancel</label>
                            <input type="text" class="form-control text-uppercase" name="nombre" placeholder="Ej. Matrícula Ciclo I" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-dark">Monto base ($)</label>
                            <input type="number" step="0.01" class="form-control" name="monto_base" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-dark">Dcto. Estudiante</label>
                            <select class="form-select" name="aplica_descuento_estudiante">
                                <option value="1">Sí, aplica</option>
                                <option value="0" selected>No aplica</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-dark">¿Es recurrente?</label>
                            <select class="form-select" name="es_recurrente">
                                <option value="1">Sí</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-dark">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <!-- Mismo texto del botón de tu imagen -->
                    <button type="submit" class="btn text-white px-4" style="background-color: #dc3545;">Sí, continuar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <!-- Detección de Errores de Validación (Código Repetido) -->
    @if($errors->has('codigo_concepto'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastHTML = `
                <div class="toast-container position-fixed top-0 end-0 p-3 mt-2 me-2" style="z-index: 1100">
                    <div id="errorToast" class="toast bg-white border-0 border-start border-4 border-danger shadow-sm rounded-end" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex p-3 align-items-start">
                            <i class="bi bi-x-circle-fill text-danger fs-5 me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Error de validación</div>
                                <div class="text-secondary" style="font-size: 0.85rem;">
                                    {{ $errors->first('codigo_concepto') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close ms-2 mb-auto" data-bs-dismiss="toast" aria-label="Close" style="font-size: 0.75rem;"></button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', toastHTML);
            const toastElement = document.getElementById('errorToast');
            const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
            toast.show();

            const createModal = new bootstrap.Modal(document.getElementById('createModal'));
            createModal.show();
        });
    </script>
    @endif
@endpush
@endsection
