@extends('layouts.app')

@section('title', 'Crear Estudiante')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         ENCABEZADO DE PÁGINA
         ============================================================ --}}
    
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
            <div class="d-flex align-items-center g-3 ">
                <div class=" px-3 py-2 rounded-3 bg-danger bg-opacity-10"
                    style="width: 48px; height: 48px;">
                    <i class="bi bi-person-plus-fill fs-4 text-danger"></i>
                </div>
                <div class="px-3">
                    <h1 class="h4 fw-bold mb-0">Crear Estudiante</h1>
                    <p class="text-muted mb-0 small">
                        Registro directo con cuenta activa &mdash; Área Académica
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('home') }}" class="btn bg-danger-subtle text-danger
                            border border-danger px-3 py-2">
                    <i class="fa-solid fa-home me-1"></i>
                    Inicio
                </a>
            </div>
        </div>

    {{-- ============================================================
         ALERTAS
         ============================================================ --}}
    @if ($errors->has('error_general'))
        <div class="alert alert-danger d-flex align-items-start gap-2 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong class="d-block mb-1">No se pudo crear el estudiante</strong>
                <span class="small">{{ $errors->first('error_general') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any() && !$errors->has('error_general'))
        <div class="alert alert-warning d-flex align-items-start gap-2 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div>
                <strong class="d-block mb-1">Revisá los datos ingresados</strong>
                <span class="small">Hay {{ $errors->count() }} campo(s) con errores.</span>
            </div>
        </div>
    @endif

    <form action="{{ route('admin-academico.students.store') }}"
          id="form-crear-estudiante"
          method="POST"
          enctype="multipart/form-data"
          x-data="validation">
        @csrf

        <div class="row g-4">

            {{-- ============================================================
                 COLUMNA IZQUIERDA
                 ============================================================ --}}
            <div class="col-lg-8">

                {{-- ================================================
                     SECCIÓN 1: DATOS PERSONALES
                     ================================================ --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-person-vcard text-danger fs-5"></i>
                        <h2 class="h6 fw-bold mb-0">Datos Personales</h2>
                    </div>
                    <div class="card-body p-4">

                        <div class="row g-3">

                            {{-- Nombre completo --}}
                            <div class="col-12">
                                <label for="name" class="form-label fw-medium small">
                                    Nombre completo <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="Ej: Juan Pérez López"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">
                                    El código de estudiante se genera automáticamente a partir de los apellidos.
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium small">
                                    Correo personal <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="usuario@ejemplo.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Regional --}}
                            <div class="col-md-6">
                                <label for="id_regional_activo" class="form-label fw-medium small">
                                    Regional / Sede <span class="text-danger">*</span>
                                </label>
                                <select name="id_regional_activo"
                                        id="id_regional_activo"
                                        class="form-select @error('id_regional_activo') is-invalid @enderror"
                                        required>
                                    <option value="">Seleccione...</option>
                                    @foreach ($regionales as $regional)
                                        <option value="{{ $regional->regional_activo_id }}"
                                            {{ old('id_regional_activo') == $regional->regional_activo_id ? 'selected' : '' }}>
                                            {{ $regional->sede }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_regional_activo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tipo documento --}}
                            <div class="col-md-4">
                                <label for="id_tipo_documento" class="form-label fw-medium small">
                                    Tipo de documento <span class="text-danger">*</span>
                                </label>
                                <select name="id_tipo_documento"
                                        id="id_tipo_documento"
                                        class="form-select @error('id_tipo_documento') is-invalid @enderror"
                                        required>
                                    <option value="">Seleccione...</option>
                                    @foreach ($tipo_documento_identidad as $tipo)
                                        <option value="{{ $tipo->tipo_documento_id }}"
                                                data-regex="{{ $tipo->formato_regex }}"
                                                data-place="{{ $tipo->formato }}"
                                                data-name="{{ $tipo->nombre }}"
                                                data-codigo = "{{ $tipo->codigo }}"
                                                {{ old('id_tipo_documento') == $tipo->tipo_documento_id ? 'selected' : '' }}>
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_tipo_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Número documento --}}
                            <div class="col-md-8">
                                <label for="documento_identidad" class="form-label fw-medium small">
                                    Número de documento <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="documento_identidad"
                                       id="documento_identidad"
                                       class="form-control @error('documento_identidad') is-invalid @enderror"
                                       value="{{ old('documento_identidad') }}"
                                       x-on:input="mascarasRequex($event)"
                                       x-on:blur="validarDocumento(true)" 
                                       required>
                                @error('documento_identidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">
                                    Ingresá el número respetando el formato del tipo seleccionado.
                                </div>
                            </div>

                            {{-- Fecha nacimiento --}}
                            <div class="col-md-4">
                                <label for="fecha_nacimiento" class="form-label fw-medium small">
                                    Fecha de nacimiento <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="fecha_nacimiento"
                                       id="fecha_nacimiento"
                                       class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                       value="{{ old('fecha_nacimiento') }}"
                                       :max="fechaLimite"
                                       required>
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Debe ser mayor de 17 años.</div>
                            </div>

                            {{-- Género --}}
                            <div class="col-md-4">
                                <label for="genero" class="form-label fw-medium small">
                                    Género <span class="text-danger">*</span>
                                </label>
                                <select name="genero"
                                        id="genero"
                                        class="form-select @error('genero') is-invalid @enderror"
                                        required>
                                    <option value="">Seleccione...</option>
                                    <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                                @error('genero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Estado civil  --}}
                            <div class="col-md-4">
                                <label for="estado_civil" class="form-label fw-medium small">Estado civil</label>
                                <select name="estado_civil"
                                        id="estado_civil"
                                        class="form-select @error('estado_civil') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="Soltero(a)"     {{ old('estado_civil') == 'Soltero(a)'     ? 'selected' : '' }}>Soltero(a)</option>
                                    <option value="Casado(a)"      {{ old('estado_civil') == 'Casado(a)'      ? 'selected' : '' }}>Casado(a)</option>
                                    <option value="Divorciado(a)"  {{ old('estado_civil') == 'Divorciado(a)'  ? 'selected' : '' }}>Divorciado(a)</option>
                                    <option value="Viudo(a)"       {{ old('estado_civil') == 'Viudo(a)'       ? 'selected' : '' }}>Viudo(a)</option>
                                </select>
                                @error('estado_civil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Celular --}}
                            <div class="col-md-6">
                                <label for="celular" class="form-label fw-medium small">Celular</label>
                                <div class="input-group">
                                    <span class="input-group-text">+503</span>
                                    <input type="text"
                                        name="celular"
                                        id="celular"
                                        class="form-control @error('celular') is-invalid @enderror"
                                        value="{{ old('celular') }}"
                                        pattern="[0-9]{4}-[0-9]{4}" 
                                        placeholder="0000-0000"
                                        maxlength="9"
                                        x-on:input="aplicarMascara($event)">
                                </div>
                                @error('celular')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dirección --}}
                            <div class="col-md-6">
                                <label for="direccion" class="form-label fw-medium small">Dirección</label>
                                <input type="text"
                                       name="direccion"
                                       id="direccion"
                                       class="form-control @error('direccion') is-invalid @enderror"
                                       value="{{ old('direccion') }}"
                                       placeholder="Ej: Col. Escalón, San Salvador">
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ================================================
                     SECCIÓN 2: INFORMACIÓN ACADÉMICA
                     ================================================ --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-mortarboard-fill text-danger fs-5"></i>
                        <h2 class="h6 fw-bold mb-0">Información Académica</h2>
                    </div>
                    <div class="card-body p-4">

                        <div class="row g-3">

                            {{-- Carrera --}}
                            <div class="col-md-8">
                                <label for="id_carrera" class="form-label fw-medium small">
                                    Carrera <span class="text-danger">*</span>
                                </label>
                                <select name="id_carrera"
                                        id="id_carrera"
                                        class="form-select @error('id_carrera') is-invalid @enderror"
                                        required>
                                    <option value="">Seleccione carrera...</option>
                                    @foreach ($carreras as $carrera)
                                        <option value="{{ $carrera->carrera_id }}"
                                            {{ old('id_carrera') == $carrera->carrera_id ? 'selected' : '' }}>
                                            {{ $carrera->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_carrera')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Rol (bloqueado) --}}
                            <div class="col-md-4">
                                <label for="rol" class="form-label fw-medium small">Rol asignado</label>
                                <select id="rol" class="form-select bg-light" disabled>
                                    <option value="ESTUDIANTE" selected>Estudiante</option>
                                </select>
                                <input type="hidden" name="rol" value="ESTUDIANTE">
                                <div class="form-text small">
                                    <i class="bi bi-lock-fill"></i>
                                    Este módulo solo permite crear estudiantes.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ================================================
                    SECCIÓN 2.5: BENEFICIO / ARANCEL
                    ================================================ --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-cash-coin text-danger fs-5"></i>
                            <h2 class="h6 fw-bold mb-0">Beneficio / Arancel</h2>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="toggle-beneficio" name="aplicar_beneficio" value="1"
                                {{ old('aplicar_beneficio') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="toggle-beneficio">
                                Asignar beneficio
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-4" id="panel-beneficio"
                        style="{{ old('aplicar_beneficio') ? '' : 'display:none;' }}">

                        <div class="alert alert-info d-flex align-items-start gap-2 border-0 small mb-4" role="alert">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>
                                El beneficio aplica a un <strong>ciclo lectivo específico</strong>. Si el estudiante
                                no califica en el siguiente ciclo, podés asignarle uno nuevo.
                            </div>
                        </div>

                        <div class="row g-3">

                            {{-- Ciclo lectivo --}}
                            <div class="col-md-6">
                                <label for="id_ciclo_lectivo" class="form-label fw-medium small">
                                    Ciclo lectivo <span class="text-danger">*</span>
                                </label>
                                <select name="id_ciclo_lectivo"
                                        id="id_ciclo_lectivo"
                                        class="form-select @error('id_ciclo_lectivo') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($ciclosLectivos as $ciclo)
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
                                <label for="tipo_beneficio" class="form-label fw-medium small">
                                    Tipo de beneficio <span class="text-danger">*</span>
                                </label>
                                <select name="tipo_beneficio"
                                        id="tipo_beneficio"
                                        class="form-select @error('tipo_beneficio') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    <option value="BECA_COMPLETA"  {{ old('tipo_beneficio') === 'BECA_COMPLETA'  ? 'selected' : '' }}>Beca Completa</option>
                                    <option value="BECA_PARCIAL"   {{ old('tipo_beneficio') === 'BECA_PARCIAL'   ? 'selected' : '' }}>Beca Parcial</option>
                                    <option value="FRANJA_BECARIA" {{ old('tipo_beneficio') === 'FRANJA_BECARIA' ? 'selected' : '' }}>Franja Becaria</option>
                                    <option value="CUOTA_ESPECIAL" {{ old('tipo_beneficio') === 'CUOTA_ESPECIAL' ? 'selected' : '' }}>Cuota Especial</option>
                                </select>
                                @error('tipo_beneficio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Nombre del convenio --}}
                            <div class="col-md-6">
                                <label for="nombre_convenio" class="form-label fw-medium small">
                                    Nombre del convenio / beca <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    name="nombre_convenio"
                                    id="nombre_convenio"
                                    class="form-control @error('nombre_convenio') is-invalid @enderror"
                                    value="{{ old('nombre_convenio') }}"
                                    placeholder="Ej: Convenio UMA-FUSAL">
                                @error('nombre_convenio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Resolución --}}
                            <div class="col-md-6">
                                <label for="resolucion_academica" class="form-label fw-medium small">
                                    Resolución académica
                                </label>
                                <input type="text"
                                    name="resolucion_academica"
                                    id="resolucion_academica"
                                    class="form-control @error('resolucion_academica') is-invalid @enderror"
                                    value="{{ old('resolucion_academica') }}"
                                    placeholder="Ej: RA-2026-014">
                                @error('resolucion_academica')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Porcentajes (solo BECA_PARCIAL y CUOTA_ESPECIAL) --}}
                            <div class="col-md-4 campo-pct" style="display:none;">
                                <label for="porcentaje_estudiante" class="form-label fw-medium small">
                                    % Estudiante
                                </label>
                                <input type="number"
                                    step="0.01" min="0" max="100"
                                    name="porcentaje_estudiante"
                                    id="porcentaje_estudiante"
                                    class="form-control @error('porcentaje_estudiante') is-invalid @enderror"
                                    value="{{ old('porcentaje_estudiante') }}">
                                @error('porcentaje_estudiante')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 campo-pct" style="display:none;">
                                <label for="porcentaje_universidad" class="form-label fw-medium small">
                                    % Universidad
                                </label>
                                <input type="number"
                                    step="0.01" min="0" max="100"
                                    name="porcentaje_universidad"
                                    id="porcentaje_universidad"
                                    class="form-control @error('porcentaje_universidad') is-invalid @enderror"
                                    value="{{ old('porcentaje_universidad') }}">
                                @error('porcentaje_universidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Monto fijo (solo FRANJA_BECARIA) --}}
                            <div class="col-md-4 campo-monto" style="display:none;">
                                <label for="monto_fijo_cuota" class="form-label fw-medium small">
                                    Monto fijo de cuota ($)
                                </label>
                                <input type="number"
                                    step="0.01" min="0"
                                    name="monto_fijo_cuota"
                                    id="monto_fijo_cuota"
                                    class="form-control @error('monto_fijo_cuota') is-invalid @enderror"
                                    value="{{ old('monto_fijo_cuota') }}"
                                    placeholder="Ej: 53.00">
                                @error('monto_fijo_cuota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ================================================
                     SECCIÓN 3: DOCUMENTACIÓN
                     ================================================ --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-folder-fill text-danger fs-5"></i>
                            <h2 class="h6 fw-bold mb-0">Documentación</h2>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success small">
                            <i class="bi bi-check-circle-fill"></i> Se aprobarán automáticamente
                        </span>
                    </div>
                    <div class="card-body p-4">

                        <div class="alert alert-info d-flex align-items-start gap-2 border-0 small mb-4" role="alert">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>
                                Como ADMIN_ACADÉMICO, los documentos que cargues quedarán registrados como
                                <strong>APROBADOS</strong> y firmados con tu usuario.
                            </div>
                        </div>

                        <div class="row g-3">

                            {{-- Título bachillerato --}}
                            <div class="col-md-6">
                                <label for="titulo_bachillerato" class="form-label fw-medium small">
                                    Título de bachillerato <span class="text-danger">*</span>
                                </label>
                                <input type="file"
                                       name="titulo_bachillerato"
                                       id="titulo_bachillerato"
                                       class="form-control @error('titulo_bachillerato') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       required>
                                @error('titulo_bachillerato')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">PDF, JPG o PNG. Máx. 2 MB.</div>
                            </div>

                            {{-- Partida nacimiento --}}
                            <div class="col-md-6">
                                <label for="partida_nacimiento" class="form-label fw-medium small">
                                    Partida de nacimiento <span class="text-danger">*</span>
                                </label>
                                <input type="file"
                                       name="partida_nacimiento"
                                       id="partida_nacimiento"
                                       class="form-control @error('partida_nacimiento') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       required>
                                @error('partida_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">PDF, JPG o PNG. Máx. 2 MB.</div>
                            </div>

                            {{-- Fotografía --}}
                            <div class="col-md-6">
                                <label for="fotografia_personal" class="form-label fw-medium small">
                                    Fotografía personal <span class="text-danger">*</span>
                                </label>
                                <input type="file"
                                       name="fotografia_personal"
                                       id="fotografia_personal"
                                       class="form-control @error('fotografia_personal') is-invalid @enderror"
                                       accept=".jpg,.jpeg,.png"
                                       required>
                                @error('fotografia_personal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">JPG o PNG. Máx. 2 MB.</div>
                            </div>

                            {{-- Constancia PAES --}}
                            <div class="col-md-6">
                                <label for="constancia_paes" class="form-label fw-medium small">
                                    Constancia PAES <span class="text-muted">(opcional)</span>
                                </label>
                                <input type="file"
                                       name="constancia_paes"
                                       id="constancia_paes"
                                       class="form-control @error('constancia_paes') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                @error('constancia_paes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">PDF, JPG o PNG. Máx. 2 MB.</div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="col-12">
                                <label for="observaciones" class="form-label fw-medium small">
                                    Observaciones
                                </label>
                                <textarea name="observaciones"
                                          id="observaciones"
                                          rows="3"
                                          class="form-control @error('observaciones') is-invalid @enderror"
                                          placeholder="Notas internas sobre este expediente...">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================================================
                 COLUMNA DERECHA: RESUMEN STICKY
                 ============================================================ --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 position-sticky" style="top: 1rem;">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard-check-fill text-danger fs-5"></i>
                        <h2 class="h6 fw-bold mb-0">Resumen del registro</h2>
                    </div>
                    <div class="card-body p-4">

                        <ul class="list-unstyled mb-0 small">

                            <li class="d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong class="d-block">Cuenta activa</strong>
                                    <span class="text-muted">El estudiante podrá iniciar sesión de inmediato.</span>
                                </div>
                            </li>

                            <li class="d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong class="d-block">Rol: Estudiante</strong>
                                    <span class="text-muted">Único rol permitido en este módulo.</span>
                                </div>
                            </li>

                            <li class="d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong class="d-block">Documentos aprobados</strong>
                                    <span class="text-muted">Firmados con tu usuario actual.</span>
                                </div>
                            </li>

                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong class="d-block">Contraseña temporal</strong>
                                    <span class="text-muted">
                                        Se genera el <strong>código de estudiante</strong> como contraseña inicial.
                                    </span>
                                </div>
                            </li>

                        </ul>

                        <hr class="my-4">

                        <div class="d-grid gap-2">
                            <button type="submit"
                                    id="btn-crear-estudiante"
                                    disabled
                                    class="btn btn-danger d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-person-check-fill"></i>
                                Crear estudiante activo
                            </button>
                        </div>

                        <p class="text-muted small mb-0 mt-3 text-center">
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </p>

                    </div>
                </div>
            </div>

        </div>

    </form>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // validacion para asignar el tipo de cuenta a un estudiante
    const toggle    = document.getElementById('toggle-beneficio');
    const panel     = document.getElementById('panel-beneficio');
    const tipoSel   = document.getElementById('tipo_beneficio');
    const pctFields = document.querySelectorAll('.campo-pct');
    const montoFld  = document.querySelector('.campo-monto');
    const pctEst    = document.getElementById('porcentaje_estudiante');
    const pctUniv   = document.getElementById('porcentaje_universidad');

    toggle?.addEventListener('change', () => {
        panel.style.display = toggle.checked ? '' : 'none';
        actualizarCamposBeneficio();
    });

    // Cambio de tipo → muestra/oculta campos
    tipoSel?.addEventListener('change', actualizarCamposBeneficio);

    function actualizarCamposBeneficio() {
        if (!toggle?.checked) return;

        const tipo = tipoSel.value;
        const esParcial = tipo === 'BECA_PARCIAL' || tipo === 'CUOTA_ESPECIAL';
        const esFranja  = tipo === 'FRANJA_BECARIA';

        pctFields.forEach(el => el.style.display = esParcial ? '' : 'none');
        montoFld.style.display = esFranja ? '' : 'none';

        // Autocompletar porcentajes sugeridos
        if (tipo === 'BECA_COMPLETA') {
            if (pctEst)  pctEst.value  = 0;
            if (pctUniv) pctUniv.value = 100;
        } else if (tipo === 'CUOTA_ESPECIAL') {
            if (pctEst && !pctEst.value)  pctEst.value  = 88.33;
            if (pctUniv && !pctUniv.value) pctUniv.value = 11.67;
        } else if (tipo === 'FRANJA_BECARIA') {
            const monto = document.getElementById('monto_fijo_cuota');
            if (monto && !monto.value) monto.value = 53.00;
        }
    }

    // Sincronizar % universidad automáticamente
    pctEst?.addEventListener('input', () => {
        if (pctUniv && pctEst.value !== '') {
            pctUniv.value = (100 - parseFloat(pctEst.value || 0)).toFixed(2);
        }
    });

    actualizarCamposBeneficio();

    // validacion para habilitar boton 

    const form = document.getElementById('form-crear-estudiante');
    const btn  = document.getElementById('btn-crear-estudiante');
    
    if (!form || !btn) return;

    const campos = Array.from(form.querySelectorAll('input, select, textarea'))
        .filter(el => el.type !== 'hidden' && !el.disabled);

    // 1. Habilita/deshabilita botón según requireds
    function revisarBoton() {
        const todosLlenos = campos
            .filter(el => el.required)
            .every(el => {
                if (el.type === 'file') return el.files.length > 0;
                return el.value.trim() !== '';
            });

        btn.disabled = !todosLlenos;
    }

    form.addEventListener('input',  revisarBoton);
    form.addEventListener('change', revisarBoton);
    revisarBoton();

    // 2. Enter = siguiente campo, no submit
    form.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        if (e.target.tagName === 'TEXTAREA') return;
        if (e.target.type === 'submit' || e.target.tagName === 'BUTTON') return;

        e.preventDefault();

        const idx = campos.indexOf(e.target);
        if (idx > -1 && campos[idx + 1]) {
            campos[idx + 1].focus();
        }
    });
});
</script>
@endpush
@endsection