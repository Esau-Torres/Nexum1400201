@extends('layouts.app')

@section('title', 'Dashboard - NEXUM')

@section('content')
<div class="container-fluid p-4">
    <h1 class="h3 mb-4">Dashboard</h1>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-2">Bienvenido</h5>
                    <p class="text-muted small mb-0">Esta es una vista de prueba para visualizar el layout.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-2">Contenido de ejemplo</h5>
                    <p class="text-muted small mb-0">El layout se muestra correctamente con la barra lateral.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-2">Prueba exitosa</h5>
                    <p class="text-muted small mb-0">Si ves la barra lateral a la izquierda, todo funciona bien.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection