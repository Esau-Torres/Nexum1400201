@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    <!-- Encabezado de la página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">REGLA DE COBRO</h2>
            <span class="text-muted">Configuración de reglas de cobro</span>
        </div>

        <!-- Contenedor de los botones (Alineados a la derecha) -->
        <div class="d-flex gap-2">

            <!-- MOTOR DE AUTO-SINCRONIZACIÓN (ESTADO Y MESES DINÁMICOS) -->
            @php
                // 1. Buscamos el ciclo activo
                $cicloActivo = \Illuminate\Support\Facades\DB::table('ciclo_lectivo')->where('estado', 'ACTIVO')->first();

                // Variables para guardar los meses que mostraremos en las listas
                $mesesDisponibles = [];

                if ($cicloActivo) {
                    // 2. Detectamos si es Ciclo 01 o Ciclo 02
                    $esCiclo01 = str_contains($cicloActivo->nombre_ciclo, '01') || $cicloActivo->numero_ciclo == '01';

                    // Asignamos la lista de meses para los modales según el ciclo
                    $mesesDisponibles = $esCiclo01
                        ? ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio']
                        : ['Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

                    $mesInscripcion = $esCiclo01 ? 'Enero' : 'Julio';

                    // 3. Encendemos todas las reglas
                    \Illuminate\Support\Facades\DB::table('reglas_pago')->update(['estado' => 'ACTIVO']);

                    // 4. AUTO-CORRECCIÓN: Forzamos la regla de Inscripción al mes correcto
                    \Illuminate\Support\Facades\DB::table('reglas_pago')
                        ->where('tipo', 'ILIKE', '%Inscripci%')
                        ->orWhere('tipo', 'ILIKE', '%Matr%')
                        ->update(['meses_aplicables' => $mesInscripcion]);

                } else {
                    // Si no hay ciclo, apagamos todo y mostramos todos los meses por defecto
                    \Illuminate\Support\Facades\DB::table('reglas_pago')->update(['estado' => 'INACTIVO']);
                    $mesesDisponibles = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                }

                // 5. Volvemos a leer las reglas ya corregidas
                $reglas = \App\Models\Adfinanciero\ReglaPago::with('conceptoRecargo')->get();
            @endphp

            <!-- Botón informativo del Ciclo Activo (Sin acción) -->
            <button type="button" class="btn btn-light border px-4 py-2" style="border-radius: 6px; cursor: default;">
                <i class="fa-regular fa-calendar-check text-success me-2"></i>
                Reglas en ejecución para:
                <span class="fw-bold text-dark ms-1">
                    {{ $cicloActivo ? $cicloActivo->nombre_ciclo . '-' . $cicloActivo->anio : 'SIN CICLO ACTIVO' }}
                </span>
            </button>

            <!-- Botón de Nueva regla -->
            <button type="button" class="btn text-white px-4 py-2" style="background-color: #dc3545; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#modalCrearRegla">
                <i class="fa-solid fa-plus me-2"></i> Nueva regla
            </button>
        </div>
    </div>

    <!-- Contenedor Principal -->
    <div class="card shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-body p-4">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted" style="background-color: #f8f9fa;">
                        <tr>
                            <th class="fw-semibold py-3 ps-3">Tipo de cobro</th>
                            <th class="fw-semibold py-3">Aplica a</th>
                            <th class="fw-semibold py-3">Periodo ordinario</th>
                            <th class="fw-semibold py-3">Periodo extraordinario</th>
                            <th class="fw-semibold py-3">Penalidad vinculada</th>
                            <th class="fw-semibold py-3 text-center">Estado</th>
                            <th class="fw-semibold py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($reglas as $regla)
                            <tr>
                                <!-- Columna: Tipo -->
                                <td class="ps-3 py-3">
                                    <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                                        {{ $regla->tipo }}
                                    </span>
                                </td>

                                <!-- Columna: Meses de aplicación -->
                                <td class="py-3">
                                    <div title="Meses en los que aplica el cobro">
                                        <span class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                                            {{-- Cambiamos el icono dependiendo de si son todos los meses o un mes específico --}}
                                            @if($regla->meses_aplicables == 'Todos los meses')
                                                <i class="fa-regular fa-calendar-check me-2 text-success"></i>
                                            @else
                                                <i class="fa-regular fa-calendar me-2 text-primary"></i>
                                            @endif

                                            {{-- Imprimimos directamente lo que viene de la base de datos --}}
                                            {{ $regla->meses_aplicables ?? 'Sin definir' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Columna: Ordinario -->
                                <td class="py-3">
                                    <div class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                                        <i class="fa-regular fa-calendar-check text-success me-2"></i>
                                        Días {{ str_pad($regla->dia_inicio_ordinario, 2, '0', STR_PAD_LEFT) }} – {{ str_pad($regla->dia_fin_ordinario, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                </td>

                                <!-- Columna: Extraordinario (Mora) -->
                                <td class="py-3">
                                    @if($regla->dia_inicio_extra && $regla->dia_fin_extra)
                                        <div class="badge bg-light text-dark border px-3 py-2 fw-normal" style="font-size: 0.85rem;">
                                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                                            Días {{ str_pad($regla->dia_inicio_extra, 2, '0', STR_PAD_LEFT) }} – {{ str_pad($regla->dia_fin_extra, 2, '0', STR_PAD_LEFT) }}
                                        </div>
                                    @else
                                        <span class="text-muted small"><i class="fa-solid fa-minus me-1"></i> No aplica</span>
                                    @endif
                                </td>

                                <!-- Columna: Concepto vinculado -->
                                <td class="py-3 text-muted" style="font-size: 0.9rem;">
                                    @if($regla->conceptoRecargo)
                                        <i class="fa-solid fa-link me-1" style="color: #5dade2;"></i>
                                        {{ $regla->conceptoRecargo->nombre }}
                                    @else
                                        Sin recargo configurado
                                    @endif
                                </td>

                                <!-- Columna: Estado -->
                                <td class="py-3 text-center">
                                    @if($regla->estado == 'ACTIVO')
                                        <span class="badge rounded-pill bg-dark px-3 py-2 fw-normal">Activo</span>
                                    @else
                                        <span class="badge rounded-pill border text-danger px-3 py-2 fw-normal">Inactivo</span>
                                    @endif
                                </td>

                                <!-- Columna: Acciones -->
                                <td class="py-3 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar regla" data-bs-toggle="modal" data-bs-target="#modalEditarRegla{{ $regla->regla_id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-folder-open fs-3 mb-3 d-block"></i>
                                    No hay reglas de pago configuradas en el sistema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- ==============================================
     ZONA DE MODALES (Fuera de la tabla principal)
=============================================== -->

<!-- MODAL: Crear Nueva Regla -->
<div class="modal fade" id="modalCrearRegla" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nueva regla de cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('reglas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de cobro <span class="text-danger">*</span></label>
                            <input type="text" name="tipo" class="form-control" placeholder="Ej. Mensualidad, Inscripción..." required>
                            <small class="text-muted">Use "Inscripción" o "Matrícula" para inicio de ciclo</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Meses aplicables <span class="text-danger">*</span></label>
                            <select name="meses_aplicables" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione los meses --</option>
                                <option value="Todos los meses">Todos los meses</option>

                                <!-- Se llena dinámicamente según el ciclo activo -->
                                @foreach($mesesDisponibles as $mes)
                                    <option value="{{ $mes }}">{{ $mes }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Periodo ordinario (inicio) <span class="text-danger">*</span></label>
                            <input type="number" name="dia_inicio_ordinario" class="form-control" min="1" max="31" placeholder="Ej. 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Periodo ordinario (fin) <span class="text-danger">*</span></label>
                            <input type="number" name="dia_fin_ordinario" class="form-control" min="1" max="31" placeholder="Ej. 10" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Periodo extraordinario (inicio)</label>
                            <input type="number" name="dia_inicio_extra" class="form-control" min="1" max="31">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Periodo extraordinario (fin)</label>
                            <input type="number" name="dia_fin_extra" class="form-control" min="1" max="31">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Penalidad a aplicar</label>
                        <select name="id_concepto_recargo" class="form-select">
                            <option value="">-- No aplica --</option>
                            @foreach($conceptos as $concepto)
                                <option value="{{ $concepto->concepto_pago_id }}">{{ $concepto->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="estado" value="ACTIVO">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #dc3545;">Guardar Regla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODALES: Editar Reglas (Se genera uno por cada regla en la tabla) -->
@foreach($reglas as $regla)
<div class="modal fade" id="modalEditarRegla{{ $regla->regla_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Editar regla: {{ $regla->tipo }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('reglas.update', $regla->regla_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de cobro <span class="text-danger">*</span></label>
                            <input type="text" name="tipo" class="form-control" value="{{ $regla->tipo }}" required>
                            <small class="text-muted"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Meses aplicables <span class="text-danger">*</span></label>
                            <select name="meses_aplicables" class="form-select" required>
                                <option value="" disabled>-- Seleccione los meses --</option>
                                <option value="Todos los meses" {{ $regla->meses_aplicables == 'Todos los meses' ? 'selected' : '' }}>Todos los meses</option>

                                <!-- Se llena dinámicamente y selecciona el que ya tiene la regla -->
                                @foreach($mesesDisponibles as $mes)
                                    <option value="{{ $mes }}" {{ $regla->meses_aplicables == $mes ? 'selected' : '' }}>{{ $mes }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Periodo ordinario (inicio) <span class="text-danger">*</span></label>
                            <input type="number" name="dia_inicio_ordinario" class="form-control" min="1" max="31" value="{{ $regla->dia_inicio_ordinario }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Periodo ordinario (fin) <span class="text-danger">*</span></label>
                            <input type="number" name="dia_fin_ordinario" class="form-control" min="1" max="31" value="{{ $regla->dia_fin_ordinario }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Periodo extraordinario (inicio)</label>
                            <input type="number" name="dia_inicio_extra" class="form-control" min="1" max="31" value="{{ $regla->dia_inicio_extra }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Periodo extraordinario (fin)</label>
                            <input type="number" name="dia_fin_extra" class="form-control" min="1" max="31" value="{{ $regla->dia_fin_extra }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Penalidad a aplicar</label>
                            <select name="id_concepto_recargo" class="form-select">
                                <option value="">-- No aplica --</option>
                                @foreach($conceptos as $concepto)
                                    <option value="{{ $concepto->concepto_pago_id }}" {{ $regla->id_concepto_recargo == $concepto->concepto_pago_id ? 'selected' : '' }}>
                                        {{ $concepto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="ACTIVO" {{ $regla->estado == 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                                <option value="INACTIVO" {{ $regla->estado == 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #dc3545;">Actualizar regla</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
