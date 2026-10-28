
@extends('layouts.app')

@push('styles')
    @vite('resources/css/pensum.css')
@endpush

@section('content')
    <div class="pensum-full-wrapper">

        {{-- Cabecera --}}
        <header class="pensum-heading">
            <div class="pensum-heading-info">
                <span class="pensum-eyebrow">MI FORMACIÓN ACADÉMICA</span>
                <h2 class="pensum-title">Pensum Académico</h2>
                <p class="pensum-subtitle">
                    Consulta tus materias, su estado académico y los prerrequisitos.
                </p>
            </div>

            <a href="{{ route('student.pensum.pdf') }}"
               class="btn pensum-download">
                <i class="bi bi-file-earmark-pdf"></i>
                <span>Descargar PDF</span>
            </a>
        </header>

        {{-- Leyenda de estados --}}
        <section class="pensum-legend-panel" aria-label="Estados de las materias">
            <span class="pensum-legend-title">ESTADOS ACADÉMICOS</span>

            <div class="pensum-legend">
                <div class="legend-item">
                    <span class="legend-box legend-actual"></span>
                    <span>Actuales</span>
                </div>

                <div class="legend-item">
                    <span class="legend-box legend-aprobada"></span>
                    <span>Aprobadas</span>
                </div>

                <div class="legend-item">
                    <span class="legend-box legend-reprobada"></span>
                    <span>Reprobadas</span>
                </div>

                <div class="legend-item">
                    <span class="legend-box legend-retirada"></span>
                    <span>Retiradas</span>
                </div>

                <div class="legend-item">
                    <span class="legend-box legend-por-cursar"></span>
                    <span>Por cursar</span>
                </div>
            </div>
        </section>

        {{-- Cuadrícula adaptable de ciclos --}}
        <section class="pensum-content" aria-label="Materias por ciclo">
            <div class="pensum-grid">

                @foreach($pensumPorCiclos as $ciclo => $materias)
                    <article class="pensum-columna">

                        <div class="ciclo-header">
                            <div class="ciclo-number">
                                <i class="bi bi-journal-bookmark"></i>
                                <span>Ciclo {{ $ciclo }}</span>
                            </div>

                            <span class="ciclo-count">
                                {{ count($materias) }}
                                {{ count($materias) === 1 ? 'materia' : 'materias' }}
                            </span>
                        </div>

                        <div class="ciclo-materias">
                            @foreach($materias as $materia)
                                @php
                                    $estado = strtoupper($materia->estado_visual ?? '');

                                    $estadoClass = match($estado) {
                                        'ACTUAL' => 'materia-actual',
                                        'APROBADA' => 'materia-aprobada',
                                        'REPROBADA' => 'materia-reprobada',
                                        'RETIRADA' => 'materia-retirada',
                                        default => 'materia-por-cursar'
                                    };

                                    $estadoTexto = match($estado) {
                                        'ACTUAL' => 'Actual',
                                        'APROBADA' => 'Aprobada',
                                        'REPROBADA' => 'Reprobada',
                                        'RETIRADA' => 'Retirada',
                                        default => 'Por cursar'
                                    };
                                @endphp

                                <button type="button"
                                    class="materia-card {{ $estadoClass }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalMateria"
                                    data-nombre="{{ $materia->nombre_materia }}"
                                    data-codigo="{{ $materia->codigo_materia }}"
                                    data-uv="{{ $materia->unidades_valorativas }}"
                                    data-ciclo="{{ $materia->ciclo_sugerido }}"
                                    data-prerequisito="{{ $materia->nombre_prerequisito ?? 'Ninguno' }}"
                                    data-uv-prerequisito="{{ $materia->uv_minimas_requeridas ?? 0 }}"
                                    data-estado="{{ $estadoTexto }}">

                                    <span class="materia-accent"></span>

                                    <span class="materia-header">
                                        <span class="materia-codigo">
                                            {{ $materia->codigo_materia }}
                                        </span>

                                        <span class="materia-uv">
                                            <i class="bi bi-award"></i>
                                            {{ $materia->unidades_valorativas }} UV
                                        </span>
                                    </span>

                                    <span class="materia-nombre">
                                        {{ $materia->nombre_materia }}
                                    </span>

                                    <span class="materia-footer">
                                        <span class="estado-indicator"></span>
                                        {{ $estadoTexto }}
                                        <i class="bi bi-arrow-up-right"></i>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </article>
                @endforeach

            </div>
        </section>
    </div>

    {{-- Modal de detalles --}}
    <div class="modal fade" id="modalMateria" tabindex="-1"
         aria-labelledby="modalMateriaNombre" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content pensum-modal">

                <div class="modal-header pensum-modal-header">
                    <div>
                        <span class="pensum-modal-eyebrow">DETALLE ACADÉMICO</span>
                        <h5 class="modal-title" id="modalMateriaNombre">Materia</h5>
                    </div>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">

                        <div class="col-6">
                            <div class="detalle-item">
                                <span class="detalle-label">Código</span>
                                <span class="detalle-valor" id="modalMateriaCodigo"></span>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="detalle-item">
                                <span class="detalle-label">Ciclo sugerido</span>
                                <span class="detalle-valor" id="modalMateriaCiclo"></span>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="detalle-item">
                                <span class="detalle-label">Unidades valorativas</span>
                                <span class="detalle-valor" id="modalMateriaUV"></span>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="detalle-item">
                                <span class="detalle-label">Estado actual</span>
                                <span class="detalle-estado" id="modalMateriaEstado"></span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="prerequisito-box">
                                <div class="prerequisito-icon">
                                    <i class="bi bi-diagram-3"></i>
                                </div>

                                <div>
                                    <strong>Prerrequisito</strong>
                                    <p id="modalMateriaPrerequisito"></p>
                                    <small>
                                        UV mínimas requeridas:
                                        <span id="modalMateriaUVPrereq"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn pensum-close"
                            data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalMateria = document.getElementById('modalMateria');

            if (!modalMateria) return;

            modalMateria.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                if (!button) return;

                const datos = {
                    nombre: button.dataset.nombre,
                    codigo: button.dataset.codigo,
                    uv: button.dataset.uv,
                    ciclo: button.dataset.ciclo,
                    prerequisito: button.dataset.prerequisito,
                    uvPrereq: button.dataset.uvPrerequisito,
                    estado: button.dataset.estado
                };

                modalMateria.querySelector('#modalMateriaNombre').textContent =
                    datos.nombre || 'Materia';

                modalMateria.querySelector('#modalMateriaCodigo').textContent =
                    datos.codigo || 'N/A';

                modalMateria.querySelector('#modalMateriaUV').textContent =
                    (datos.uv || '0') + ' UV';

                modalMateria.querySelector('#modalMateriaCiclo').textContent =
                    datos.ciclo ? 'Ciclo ' + datos.ciclo : 'No especificado';

                modalMateria.querySelector('#modalMateriaEstado').textContent =
                    datos.estado || 'Por cursar';

                modalMateria.querySelector('#modalMateriaPrerequisito').textContent =
                    datos.prerequisito || 'Ninguno';

                modalMateria.querySelector('#modalMateriaUVPrereq').textContent =
                    datos.uvPrereq || '0';
            });
        });
    </script>
@endpush
