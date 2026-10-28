@php
    /** @var \App\Models\Estudiante\Alumnos $solicitud */
    $user = $solicitud->usuario;
@endphp

<div class="p-4">

    {{-- =========================================================
         SECCIÓN 1: DATOS DEL SOLICITANTE
         ========================================================= --}}
    <div class="mb-4">
        <h6 class="text-danger fw-bold mb-3">
            <i class="bi bi-person-vcard me-1"></i> Datos Personales
        </h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Nombre completo</small>
                    <span class="fw-semibold">{{ $user->name }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Email</small>
                    <span class="fw-medium">{{ $user->email }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Documento</small>
                    <span class="fw-medium">
                        {{ $user->tipodocumentoidentidad->nombre ?? 'N/A' }}:
                        {{ $user->documento_identidad }}
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Fecha nacimiento</small>
                    <span class="fw-medium">
                        {{ $user->fecha_nacimiento?->format('d/m/Y') }} ({{ $user->fecha_nacimiento?->age }} años)
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Género</small>
                    <span class="fw-medium">{{ $user->genero === 'M' ? 'Masculino' : 'Femenino' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Estado civil</small>
                    <span class="fw-medium">{{ $user->estado_civil ?? '—' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Celular</small>
                    <span class="fw-medium">{{ $user->celular ?? '—' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Sede</small>
                    <span class="fw-medium">{{ $user->regionalactivo->sede ?? '—' }}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="border rounded-3 p-3 bg-light">
                    <small class="text-muted d-block mb-1">Dirección</small>
                    <span class="fw-medium">{{ $user->direccion ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SECCIÓN 2: DATOS ACADÉMICOS
         ========================================================= --}}
    <div class="mb-4">
        <h6 class="text-danger fw-bold mb-3">
            <i class="bi bi-mortarboard me-1"></i> Información Académica
        </h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Carrera</small>
                    <span class="fw-semibold">{{ $solicitud->carrera->nombre ?? '—' }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Facultad</small>
                    <span class="fw-medium">{{ $solicitud->carrera->facultad->nombre ?? '—' }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Código de estudiante</small>
                    <span class="fw-semibold font-monospace">{{ $solicitud->codigo_estudiante }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <small class="text-muted d-block mb-1">Correo institucional</small>
                    <span class="fw-medium">{{ $solicitud->correo_institucional }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SECCIÓN 3: DOCUMENTOS
         ========================================================= --}}
    <div class="mb-4">
        <h6 class="text-danger fw-bold mb-3">
            <i class="bi bi-folder2-open me-1"></i> Documentos Adjuntos
        </h6>
        @php $docs = $solicitud->documentos; @endphp

        @if($docs)
            <div class="row g-3">
                @php
                    $archivos = [
                        'titulo_bachillerato' => ['Título de Bachillerato', 'bi-file-earmark-text'],
                        'partida_nacimiento'  => ['Partida de Nacimiento',   'bi-file-earmark-text'],
                        'fotografia_personal' => ['Fotografía Personal',     'bi-person-badge'],
                        'constancia_paes'     => ['Constancia PAES',         'bi-file-earmark-check'],
                    ];
                @endphp

                @foreach($archivos as $campo => [$label, $icon])
                    @if($docs->$campo)
                        @php
                            $esFotoPublica = $campo === 'fotografia_personal';
                            $extension     = strtolower(pathinfo($docs->$campo, PATHINFO_EXTENSION));
                            $esImagen      = in_array($extension, ['jpg', 'jpeg', 'png'], true);

                            $url = $esFotoPublica
                                ? asset('storage/' . $docs->$campo)
                                : route('admin-academico.documentos.download', ['alumno' => $solicitud->alumno_id, 'campo' => $campo]);
                        @endphp
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <small class="text-muted fw-semibold">
                                        <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                                    </small>
                                    <span class="badge bg-success-subtle text-success border border-success small">
                                        Cargado
                                    </span>
                                </div>

                                @if($esImagen)
                                    <a href="{{ $url }}" target="_blank" class="d-block">
                                        <img src="{{ $url }}"
                                            alt="{{ $label }}"
                                            class="img-fluid rounded border"
                                            style="max-height: 180px; object-fit: contain; width: 100%;">
                                    </a>
                                @else
                                    <a href="{{ $url }}" target="_blank"
                                    class="btn btn-sm btn-outline-danger w-100">
                                        <i class="bi bi-download me-1"></i> Ver PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!empty($docs->observaciones))
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-chat-left-text text-danger"></i>
                                <small class="text-muted fw-semibold">Observaciones del expediente</small>
                            </div>
                            <p class="mb-0 fw-medium small" style="white-space: pre-wrap;">{{ $docs->observaciones }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="alert alert-warning border-0 small">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                No se encontraron documentos cargados.
            </div>
        @endif
    </div>

    {{-- =========================================================
         SECCIÓN 4: APROBAR CON BENEFICIO
         ========================================================= --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="text-danger fw-bold mb-0">
                <i class="bi bi-check2-circle me-1"></i> Aprobación de la solicitud
            </h6>
        </div>
        <div class="card-body">

            <form action="{{ route('admin-academico.admin.academico.solicitudes.approve', $solicitud) }}"
                  method="POST" id="form-aprobar">
                @csrf

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="aplicar_beneficio" name="aplicar_beneficio" value="1">
                    <label class="form-check-label fw-medium" for="aplicar_beneficio">
                        Asignar beneficio / beca a este estudiante
                    </label>
                </div>

                <div id="panel-beneficio" style="display:none;">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="id_ciclo_lectivo" class="form-label fw-medium small">
                                Ciclo lectivo <span class="text-danger">*</span>
                            </label>
                            <select name="id_ciclo_lectivo" id="id_ciclo_lectivo" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($ciclosLectivos as $ciclo)
                                    <option value="{{ $ciclo->ciclo_lectivo_id }}">
                                        {{ $ciclo->nombre_ciclo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo_beneficio" class="form-label fw-medium small">
                                Tipo de beneficio <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_beneficio" id="tipo_beneficio" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="BECA_COMPLETA">Beca Completa</option>
                                <option value="BECA_PARCIAL">Beca Parcial</option>
                                <option value="FRANJA_BECARIA">Franja Becaria</option>
                                <option value="CUOTA_ESPECIAL">Cuota Especial</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="nombre_convenio" class="form-label fw-medium small">
                                Nombre del convenio <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre_convenio" id="nombre_convenio"
                                   class="form-control" placeholder="Ej: Convenio UMA-FUSAL">
                        </div>

                        <div class="col-md-6">
                            <label for="resolucion_academica" class="form-label fw-medium small">
                                Resolución
                            </label>
                            <input type="text" name="resolucion_academica" id="resolucion_academica"
                                   class="form-control" placeholder="Ej: RA-2026-014">
                        </div>

                        <div class="col-md-4 campo-pct" style="display:none;">
                            <label for="porcentaje_estudiante" class="form-label fw-medium small">% Estudiante</label>
                            <input type="number" step="0.01" min="0" max="100"
                                   name="porcentaje_estudiante" id="porcentaje_estudiante"
                                   class="form-control">
                        </div>

                        <div class="col-md-4 campo-pct" style="display:none;">
                            <label for="porcentaje_universidad" class="form-label fw-medium small">% Universidad</label>
                            <input type="number" step="0.01" min="0" max="100"
                                   name="porcentaje_universidad" id="porcentaje_universidad"
                                   class="form-control">
                        </div>

                        <div class="col-md-4 campo-monto" style="display:none;">
                            <label for="monto_fijo_cuota" class="form-label fw-medium small">Monto fijo ($)</label>
                            <input type="number" step="0.01" min="0"
                                   name="monto_fijo_cuota" id="monto_fijo_cuota"
                                   class="form-control" placeholder="Ej: 53.00">
                        </div>
                        {{-- Opciones de cobertura adicional (según tipo de beneficio) --}}
                        {{-- MATRÍCULA: aplica para BECA_PARCIAL y CUOTA_ESPECIAL --}}
                        <div class="col-12 campo-matricula" style="display:none;">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="incluye_matricula"
                                        name="incluye_matricula"
                                        value="1"
                                        {{ old('incluye_matricula') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium" for="incluye_matricula">
                                        Incluir <strong>matrícula del ciclo</strong> en la cobertura
                                    </label>
                                </div>
                                <div class="form-text small ms-5">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Marcar solo si la <strong>resolución o convenio</strong> lo autoriza expresamente.
                                </div>
                            </div>
                        </div>

                        {{-- LABORATORIO: aplica para BECA_COMPLETA --}}
                        <div class="col-12 campo-laboratorio" style="display:none;">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="incluye_laboratorio"
                                        name="incluye_laboratorio"
                                        value="1"
                                        {{ old('incluye_laboratorio') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium" for="incluye_laboratorio">
                                        Incluir <strong>laboratorio de informática</strong> en la cobertura
                                    </label>
                                </div>
                                <div class="form-text small ms-5">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Marcar solo si el <strong>pensum de la carrera</strong> exige laboratorio.
                                </div>
                            </div>
                        </div>

                        {{-- DERECHOS DE GRADO: nunca aplica según tu matriz, pero lo dejamos por si cambia --}}
                        <div class="col-12 campo-derechos" style="display:none;">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="incluye_derechos_grado"
                                        name="incluye_derechos_grado"
                                        value="1"
                                        {{ old('incluye_derechos_grado') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium" for="incluye_derechos_grado">
                                        Incluir <strong>derechos de grado</strong> en la cobertura
                                    </label>
                                </div>
                                <div class="form-text small ms-5">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Generalmente excluido. Marcar solo bajo autorización de Rectoría.
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <hr class="my-4">

                {{-- Sede asignada --}}
                <div class="mb-4">
                    <label for="id_regional_activo" class="form-label fw-medium small">
                        Sede asignada <span class="text-danger">*</span>
                    </label>
                    <select name="id_regional_activo"
                            id="id_regional_activo"
                            class="form-select"
                            required>
                        <option value="">— Seleccionar sede —</option>
                        @foreach($regionales as $regional)
                            <option value="{{ $regional->regional_activo_id }}"
                                {{ old('id_regional_activo', $user->id_regional_activo) == $regional->regional_activo_id ? 'selected' : '' }}>
                                {{ $regional->sede }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text small">
                        <i class="bi bi-info-circle me-1"></i>
                        Confirmá la sede donde el estudiante cursará su carrera.
                    </div>
                </div>

                <div class="d-flex justify-content-between flex-wrap gap-2">

                    <button type="button"
                            class="btn btn-outline-danger"
                            data-bs-toggle="collapse"
                            data-bs-target="#panel-rechazo">
                        <i class="bi bi-x-circle me-1"></i> Rechazar solicitud
                    </button>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Aprobar Solicitud
                    </button>
                </div>

            </form>

            {{-- Panel de rechazo --}}
            <div class="collapse mt-3" id="panel-rechazo">
                <div class="border border-danger rounded-3 p-3 bg-danger bg-opacity-10">
                    <form action="{{ route('admin-academico.admin.academico.solicitudes.reject', $solicitud) }}"
                          method="POST"
                          onsubmit="return confirm('¿Confirmás rechazar y ELIMINAR esta solicitud? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')

                        <label for="motivo_rechazo" class="form-label fw-medium small text-danger">
                            Motivo del rechazo <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo_rechazo" id="motivo_rechazo" rows="3"
                                  class="form-control mb-3"
                                  placeholder="Ej: Documentos ilegibles / datos inconsistentes..."
                                  required minlength="10" maxlength="500"></textarea>

                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Confirmar rechazo y eliminar
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
(function() {
    const toggle    = document.getElementById('aplicar_beneficio');
    const panel     = document.getElementById('panel-beneficio');
    const tipoSel   = document.getElementById('tipo_beneficio');
    const pctFields = document.querySelectorAll('.campo-pct');
    const montoFld  = document.querySelector('.campo-monto');
    const pctEst    = document.getElementById('porcentaje_estudiante');
    const pctUniv   = document.getElementById('porcentaje_universidad');

    const wrapMatricula = document.querySelector('.campo-matricula');
    const wrapLaboratorio = document.querySelector('.campo-laboratorio');
    const wrapDerechos  = document.querySelector('.campo-derechos');

    const chkMatricula  = document.getElementById('incluye_matricula');
    const chkLaboratorio = document.getElementById('incluye_laboratorio');
    const chkDerechos   = document.getElementById('incluye_derechos_grado');

    toggle?.addEventListener('change', () => {
        panel.style.display = toggle.checked ? '' : 'none';
        actualizar();
    });

    tipoSel?.addEventListener('change', actualizar);

    function actualizar() {
        const tipo = tipoSel.value;
        const esParcial = tipo === 'BECA_PARCIAL' || tipo === 'CUOTA_ESPECIAL';
        const esFranja  = tipo === 'FRANJA_BECARIA';
        const esCompleta = tipo === 'BECA_COMPLETA';

        pctFields.forEach(el => el.style.display = esParcial ? '' : 'none');
        montoFld.style.display = esFranja ? '' : 'none';

        if (tipo === 'BECA_COMPLETA') {
            if (pctEst)  pctEst.value  = 0;
            if (pctUniv) pctUniv.value = 100;
        } else if (tipo === 'CUOTA_ESPECIAL' && pctEst && !pctEst.value) {
            pctEst.value  = 88.33;
            pctUniv.value = 11.67;
        } else if (esFranja && montoFld && !document.getElementById('monto_fijo_cuota').value) {
            document.getElementById('monto_fijo_cuota').value = 53.00;
        }

        // Checkboxes condicionales
        wrapMatricula.style.display   = (esParcial)  ? '' : 'none';
        wrapLaboratorio.style.display = (esCompleta) ? '' : 'none';
        wrapDerechos.style.display    = 'none';

        // Al ocultar, desmarcar y limpiar para que no se envíen valores residuales
        if (!esParcial && chkMatricula)  { chkMatricula.checked = false; }
        if (!esCompleta && chkLaboratorio) { chkLaboratorio.checked = false; }
        if (chkDerechos) { chkDerechos.checked = false; } // nunca aplica
    }

    pctEst?.addEventListener('input', () => {
        if (pctUniv && pctEst.value !== '') {
            pctUniv.value = (100 - parseFloat(pctEst.value || 0)).toFixed(2);
        }
    });

    actualizar();
})();
</script>