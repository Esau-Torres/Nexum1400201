@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')

<div class="container-fluid px-3 px-md-4 py-4">
<div class="mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 rounded-4 card">
            <div>
                <h3 class="fw-semibold mb-1">
                    CREAR NUEVOS USUARIOS
                </h3>

                <p class="text-muted mb-0">
                    Gestiona la información personal, identificación y estado
                    de la cuenta del usuario.
                </p>
            </div>

            <div>
                <a href="{{ route('superadmin.panel-administrativo') }}" class="btn bg-danger-subtle text-danger
                            border border-danger px-3 py-2">
                    <i class="bi bi-person-gear me-1"></i>
                    Gestión de usuario
                </a>
            </div>
    </div>
</div>



<div class="card border-1 shadow-x rounded-4">

    <div class="card-body p-3 p-md-4 p-xl-5">

        <form action="{{ route('superadmin.users.store') }}" method="POST" novalidate  x-data="validation">
            @csrf
            <div class="mb-4">
                <div class="d-flex align-items-center mb-3">

                    <div class="bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px;">
                        <i class="bi bi-person-vcard fs-5 text-danger"></i>
                    </div>

                    <div class="ms-3">
                        <h5 class="fw-semibold mb-0">
                            INFORMACÓN PERSONAL
                        </h5>
                        <small class="text-muted">
                            Datos principales del usuario
                        </small>
                    </div>
                </div>


                <div class="row g-3 mt-4">
                    <div class="col-12">
                        <label for="name" class="form-label fw-medium">
                            Nombre completo <span class="text-danger">*</span>
                        </label>

                        <input type="text" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Ingrese el nombre completo" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label fw-medium">
                            Correo electrónico <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="ejemplo@correo.com" required  value="{{ old('email') }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Celular <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+503</span>
                            <input type="text" name="celular" id="celular" class="form-control" value="{{ old('celular') }}" pattern="[0-9]{4}-[0-9]{4}" placeholder="0000-0000" maxlength="9" x-on:input="aplicarMascara($event)" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label small fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento') }}" :max="fechaLimite" required>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">  
                        <label class="form-label small fw-semibold">Género <span class="text-danger">*</span></label>
                        <select name="genero" id="genero" class="form-select soft-input @error('genero') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label small fw-semibold">Estado Civil <span class="text-danger">*</span></label>
                        <select name="estado_civil" id="estado_civil" class="form-select soft-input @error('estado_civil') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach(['Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)'] as $ec)
                                <option value="{{ $ec }}" {{ old('estado_civil') == $ec ? 'selected' : '' }}>{{ $ec }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="direccion" class="form-label fw-medium">Dirección <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="3" placeholder="Ingrese la dirección de residencia"></textarea>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px;">
                        <i class="bi bi-card-heading fs-5 text-danger"></i>
                    </div>

                    <div class="ms-3">
                        <h5 class="fw-semibold mb-0">
                            INFORMACIÓN DE IDENTIFICACIÓN
                        </h5>
                        <small class="text-muted">
                            Datos utilizados para identificar al usuario
                        </small>
                    </div>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-12 col-md-6">
                        <label for="id_regional_activo" class="form-label fw-medium">Regional activa <span class="text-danger">*</span></label>

                        <select class="form-select" id="id_regional_activo" name="id_regional_activo" >
                            <option value="" selected>
                                Seleccione una regional
                            </option>
                            @foreach($regionales as $RG)
                                <option value="{{ $RG->regional_activo_id }}">
                                    {{$RG->sede}}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
                        <select name="id_tipo_documento" id="id_tipo_documento" class="form-select @error('id_tipo_documento') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                             @foreach($tiposDocumento as $tipo)
                                <option
                                    value="{{ $tipo->tipo_documento_id }}"
                                    data-regex="{{ $tipo->formato_regex }}"
                                    data-place="{{ $tipo->formato }}"
                                    data-name="{{ $tipo->nombre }}"
                                    data-codigo = "{{ $tipo->codigo }}"
                                    {{ old('id_tipo_documento') == $tipo->tipo_documento_id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Número de Documento <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="documento_identidad"
                            id="documento_identidad"
                            class="form-control @error('documento_identidad') is-invalid @enderror"
                            value="{{ old('documento_identidad') }}"
                            x-on:input="mascarasRequex($event)"
                            x-on:blur="validarDocumento(true)" 
                            required>
                        @error('documento_identidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>


            <hr class="my-4">

            <div class="mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px;">
                        <i class="bi bi-person-check fs-5 text-danger"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-semibold mb-0">
                            CONFIGURACIÓN DE CUENTA
                        </h5>
                        <small class="text-muted">
                            Configura el estado y los roles asignados al usuario
                        </small>
                    </div>
                </div>


                <div class="row g-3 mt-4">
                    <div class="col-12 col-md-6">
                        <label for="estado" class="form-label fw-medium">Estado <span class="text-danger">*</span></label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="" selected> Seleccione un estado </option>
                            <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}> Activo </option>
                            <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}> Inactivo </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="selectorRol" class="form-label fw-medium">
                            Roles del usuario
                            <span class="form-text">(Puede asignar uno o varios roles al usuario.)</span> <span class="text-danger">*</span>
                        </label>

                        {{-- Select nativo pero oculto visualmente (se envía al backend) --}}
                        <select class="d-none" id="selectorRol" name="roles[]" multiple>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->rol_id }}"
                                        data-nombre="{{ $rol->nombre }}"
                                        {{ in_array($rol->rol_id, old('roles', [])) ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>

                        {{-- UI visible: dropdown de Bootstrap --}}
                        <div class="dropdown">
                            <button class="form-select text-start d-flex justify-content-between align-items-center"
                                    type="button"
                                    id="dropdownRoles"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside">
                                <span id="textoDropdown" class="text-muted">Seleccione uno o más roles</span>
                                <i class="bi bi-chevron-down small"></i>
                            </button>

                            <ul class="dropdown-menu w-100 p-2 shadow-sm" style="max-height: 320px; overflow-y: auto;">
                                @foreach ($roles as $rol)
                                    <li>
                                        <label class="dropdown-item rounded d-flex align-items-start gap-2 py-2">
                                            <input class="form-check-input mt-1 rol-check"
                                                type="checkbox"
                                                value="{{ $rol->rol_id }}"
                                                data-nombre="{{ $rol->nombre }}"
                                                {{ in_array($rol->rol_id, old('roles', [])) ? 'checked' : '' }}>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium small">{{ $rol->nombre }}</div>
                                                <div class="text-muted" style="font-size: .75rem;">
                                                    {{ Str::limit($rol->descripcion, 60) }}
                                                </div>
                                            </div>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @error('roles')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Panel de chips --}}
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="fw-medium small text-uppercase text-muted">
                                    <i class="bi bi-person-badge me-1"></i> Roles seleccionados
                                </span>
                                <span id="contadorRoles" class="badge bg-secondary-subtle text-secondary border">0 roles</span>
                            </div>
                            <div id="rolesSeleccionados" class="d-flex flex-wrap gap-2">
                                <span id="sinRoles" class="text-muted small fst-italic">No hay roles seleccionados.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-top pt-4 mt-4">
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-check-lg me-1"></i>
                        Guardar usuario
                    </button>
                </div>
            </div>
        </form>

    </div>

</div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const select        = document.getElementById('selectorRol');
        const checkboxes    = document.querySelectorAll('.rol-check');
        const chips         = document.getElementById('rolesSeleccionados');
        const contador      = document.getElementById('contadorRoles');
        const sinRoles      = document.getElementById('sinRoles');
        const textoDropdown = document.getElementById('textoDropdown');

        function sincronizarSelect() {
            const ids = Array.from(checkboxes).filter(c => c.checked).map(c => c.value);
            Array.from(select.options).forEach(opt => {
                opt.selected = ids.includes(opt.value);
            });
        }

        function renderizar() {
            chips.querySelectorAll('.chip-rol').forEach(el => el.remove());

            const seleccionados = Array.from(checkboxes).filter(c => c.checked);
            sinRoles.style.display = seleccionados.length === 0 ? 'inline' : 'none';

            if (seleccionados.length === 0) {
                textoDropdown.textContent = 'Seleccione uno o más roles';
                textoDropdown.classList.add('text-muted');
            } else if (seleccionados.length === 1) {
                textoDropdown.textContent = seleccionados[0].dataset.nombre;
                textoDropdown.classList.remove('text-muted');
            } else {
                textoDropdown.textContent = `${seleccionados.length} roles seleccionados`;
                textoDropdown.classList.remove('text-muted');
            }

            seleccionados.forEach(cb => {
                const chip = document.createElement('span');
                chip.className = 'chip-rol badge rounded-pill bg-danger-subtle text-danger border border-danger px-3 py-2 d-inline-flex align-items-center gap-2 fw-normal';
                chip.innerHTML = `
                    <i class="bi bi-person-badge"></i>
                    <span>${cb.dataset.nombre}</span>
                    <button type="button" class="btn btn-sm p-0 text-danger">
                        <i class="bi bi-x-lg"></i>
                    </button>
                `;
                chip.querySelector('button').addEventListener('click', () => {
                    cb.checked = false;
                    sincronizarSelect();
                    renderizar();
                });
                chips.appendChild(chip);
            });

            const n = seleccionados.length;
            contador.textContent = `${n} ${n === 1 ? 'rol' : 'roles'}`;
            sincronizarSelect();
        }

        checkboxes.forEach(cb => cb.addEventListener('change', renderizar));
        renderizar();
    });
</script>
@endpush
@endsection