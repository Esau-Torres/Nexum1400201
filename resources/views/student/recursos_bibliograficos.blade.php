@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-collection-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h2 class="h4 fw-bold mb-0">RECURSOS BIBLIOGRÁFICOS</h2>
                <p class="text-muted mb-0 small">
                    Accede a las bases de datos y catálogos de la universidad.
                </p>
            </div>
        </div>
        <span class="badge bg-danger">
            {{ count($recursos) }} recursos disponibles
        </span>
    </div>

    {{-- AVISO --}}
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill fs-4"></i>
            <div>
                <strong class="d-block mb-1">Sistemas externos</strong>
                <p class="mb-0 small">
                    Estos recursos son administrados por la Biblioteca de la universidad.
                    Se abrirán en una nueva pestaña y pueden requerir credenciales institucionales
                    o conexión a la red de la universidad.
                </p>
            </div>
        </div>
    </div>

    {{-- GRID DE RECURSOS --}}
    <div class="row g-4">
        @foreach($recursos as $r)
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm portal-hover" style="transition:all .25s;">
                    <div class="card-body d-flex flex-column">

                        {{-- Ícono --}}
                        <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3"
                             style="width:56px;height:56px;background:{{ $r['bg'] }};">
                            <i class="bi {{ $r['icon'] }} fs-4" style="color:{{ $r['color'] }};"></i>
                        </div>

                        {{-- Nombre + categoría --}}
                        <h6 class="fw-bold mb-1">{{ $r['nombre'] }}</h6>
                        <span class="badge align-self-start mb-2"
                              style="background:{{ $r['bg'] }};color:{{ $r['color'] }};">
                            {{ $r['categoria'] }}
                        </span>

                        {{-- Descripción --}}
                        <p class="text-muted small mb-4 flex-grow-1">
                            {{ $r['descripcion'] }}
                        </p>

                        {{-- Botón --}}
                        <a href="{{ $r['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1"
                           style="background:{{ $r['color'] }};color:#fff;border-color:{{ $r['color'] }};">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Abrir recurso
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- AYUDA ADICIONAL --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body d-flex align-items-start gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-light"
                 style="width:44px;height:44px;flex-shrink:0;">
                <i class="bi bi-life-preserver fs-5 text-danger"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1">¿Problemas para acceder?</h6>
                <p class="text-muted small mb-0">
                    Si tienes dificultades con las credenciales o la conexión a algún recurso,
                    contacta al personal de la Biblioteca de la universidad o consulta con
                    Administración Académica a través del
                    <a href="{{ route('student.situaciones.index') }}" class="text-danger fw-semibold">
                        Buzón de Situaciones
                    </a>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection