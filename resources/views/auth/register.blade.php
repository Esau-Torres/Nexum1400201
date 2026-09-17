<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>
    @vite(['resources/css/app.css','resources/css/toast.css' , 'resources/js/app.js'])
</head>
<body >
    <div class="d-flex align-items-center justify-content-center min-vh-100 py-5" style="background-color: var(--bg-main);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-7">
                    
                    <!-- Encabezado -->
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark">Solicitud de Ingreso</h4>
                        <p class="text-secondary small">Completa tu expediente para validación académica.</p>
                    </div>

                    <!-- Contenedor del Stepper con Alpine.js -->
                    <div class="card border-0 shadow-sm rounded-4 bg-white" x-data="registroStepper()">
                        
                        <!-- Indicadores de Progreso -->
                        <div class="card-header bg-white border-bottom p-4">
                            <div class="d-flex justify-content-between position-relative">
                                <!-- Línea conectora -->
                                <div class="position-absolute top-50 start-0 w-100 translate-middle-y" style="height: 3px; background-color: var(--color-hover-bg); z-index: 1;"></div>
                                
                                <template x-for="i in 3" :key="i">
                                    <div class="position-relative z-3 d-flex flex-column align-items-center bg-white px-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold transition-all" 
                                            style="width: 40px; height: 40px; border: 2px solid;"
                                            :style="step >= i ? 'background-color: var(--color-accent); color: white; border-color: var(--color-accent);' : 'background-color: white; color: #6c757d; border-color: #dee2e6;'">
                                            <span x-text="i" class="px-2"></span>
                                        </div>
                                        <span class="small mt-2 fw-semibold" 
                                            :class="step >= i ? 'text-dark' : 'text-muted'"
                                            x-text="getStepTitle(i)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registroForm">
                                @csrf

                                <!-- ==========================================
                                    PASO 1: DATOS PERSONALES
                                =========================================== -->
                                <div x-show="step === 1" x-transition.opacity>
                                    <h5 class="fw-bold mb-4" style="color: var(--color-accent);">1. Información Personal</h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Correo Personal (Contacto) <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6" x-data="validation">
                                            <label class="form-label small fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento') }}" :max="fechaLimite" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Género <span class="text-danger">*</span></label>
                                            <select name="genero" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                                                <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Estado Civil <span class="text-danger">*</span></label>
                                            <select name="estado_civil" class="form-select">
                                                <option value="">Seleccione...</option>
                                                <option value="Soltero(a)">Soltero(a)</option>
                                                <option value="Casado(a)">Casado(a)</option>
                                                <option value="Divorciado(a)">Divorciado(a)</option>
                                                <option value="Viudo(a)">Viudo(a)</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4" x-data="validation">
                                            <label class="form-label small fw-semibold">Celular <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">+503</span>
                                                <input type="text" name="celular" id="celular" class="form-control" value="{{ old('celular') }}" pattern="[0-9]{4}-[0-9]{4}" placeholder="0000-0000" maxlength="9" x-on:input="aplicarMascara($event)" required>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Dirección de Residencia <span class="text-danger">*</span></label>
                                            <textarea name="direccion" class="form-control" rows="2">{{ old('direccion') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==========================================
                                    PASO 2: IDENTIDAD Y ACADEMIA
                                =========================================== -->
                                <div x-show="step === 2" x-transition.opacity style="display: none;">
                                    <h5 class="fw-bold mb-4" style="color: var(--color-accent);">2. Identidad y Carrera</h5>
                                    
                                    <div class="row g-3" x-data="validation">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
                                            <select name="id_tipo_documento" id="id_tipo_documento" class="form-select @error('id_tipo_documento') is-invalid @enderror" required>
                                                <option value="">Seleccione...</option>
                                                 @foreach($tipo_documento_identidad as $tipo)
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

                                        <div class="col-md-6">
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

                                        <div class="col-12 mt-4">
                                            <label class="form-label small fw-semibold">Carrera a Inscribir <span class="text-danger">*</span></label>
                                            <select name="id_carrera" class="form-select form-select-lg @error('id_carrera') is-invalid @enderror" required>
                                                <option value="">Seleccione la carrera de su interés...</option>
                                                @foreach($carreras as $carrera)
                                                    <option class="small" value="{{ $carrera->carrera_id  }}" {{ old('id_carrera') == $carrera->carrera_id  ? 'selected' : '' }}>{{ $carrera->nombre }}</option>    
                                                @endforeach
                                            </select>
                                            @error('id_carrera')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- ==========================================
                                    PASO 3: DOCUMENTACIÓN FÍSICA
                                =========================================== -->
                                <div x-show="step === 3" x-transition.opacity style="display: none;">
                                    <h5 class="fw-bold mb-4" style="color: var(--color-accent);">3. Anexos y Documentación</h5>
                                    <div class="alert bg-light border-start border-3 border-info small">
                                        Formatos permitidos: PDF, JPG, PNG. Tamaño máximo por archivo: 2MB.
                                    </div>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Título de Bachiller <span class="text-danger">*</span></label>
                                            <input type="file" name="titulo_bachillerato" class="form-control @error('titulo_bachillerato') is-invalid @enderror" accept=".pdf,.jpg,.png" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Partida de Nacimiento <span class="text-danger">*</span></label>
                                            <input type="file" name="partida_nacimiento" class="form-control @error('partida_nacimiento') is-invalid @enderror" accept=".pdf,.jpg,.png" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Fotografía Personal <span class="text-danger">*</span></label>
                                            <input type="file" name="fotografia_personal" class="form-control @error('fotografia_personal') is-invalid @enderror" accept=".jpg,.png" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Constancia PAES / AVANZO </label>
                                            <input type="file" name="constancia_paes" class="form-control" accept=".pdf,.jpg,.png">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Observaciones <span class="text-danger">*</span></label>
                                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Controles de Navegación -->
                                <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" 
                                            x-show="step > 1" 
                                            @click="prevStep">
                                        <i class="bi bi-arrow-left me-2"></i> Anterior
                                    </button>
                                    
                                    <div class="ms-auto">
                                        <button type="button" class="btn text-white px-4 fw-semibold" 
                                                style="background-color: var(--color-accent);"
                                                x-show="step < 3" 
                                                @click="nextStep">
                                            Siguiente <i class="bi bi-arrow-right ms-2"></i>
                                        </button>
                                        
                                        <button type="submit" class="btn btn-dark px-4 fw-semibold" 
                                                x-show="step === 3">
                                            <i class="bi bi-send-check me-2"></i> Enviar Solicitud
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-decoration-none small text-secondary fw-semibold">
                            ¿Ya posees credenciales? <span class="text-primary text-decoration-underline">Iniciar sesión aquí</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // validacion del formato del dui
        function checksumDUI(valor) {
            const limpio = valor.replace(/-/g, '').trim();
            if (limpio.length !== 9 || !/^\d+$/.test(limpio)) return false;

            let suma = 0;
            for (let i = 0; i < 8; i++) {
                suma += parseInt(limpio[i], 10) * (9 - i);
            }
            return (10 - (suma % 10)) % 10 === parseInt(limpio[8], 10);
        }

        // validacion extra del pasaporte en tiempo real 
        function validarPasaporte(valor) {
            const limpio = valor.trim();
            return /^[A-Za-z]\d{8}$/.test(limpio);
        }

        // Clave = tipo_documento_id (DUI = DUI, PORT = Pasaporte, CDN = Minoridad)
        const VALIDACION_EXTRA = {
            'DUI': checksumDUI,
            'PORT': validarPasaporte,
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('registroStepper', () => ({
                
                step: 1,
                titles: ['Datos Personales', 'Academia', 'Documentos'],
                
                getStepTitle(index) {
                    return this.titles[index - 1];
                },
                
                nextStep() {
                    // Selecciona únicamente el contenedor del paso activo actual
                    const currentStepEl = document.querySelector(`div[x-show="step === ${this.step}"]`);
                    const inputs = currentStepEl.querySelectorAll('input, select, textarea');
                    
                    let isValid = true;
                    for (let input of inputs) {
                        if (!input.checkValidity()) {
                            input.reportValidity();
                            isValid = false;
                            break;
                        }
                    }

                    if (isValid && this.step < 3) {
                        this.step++;
                    }
                },
                
                prevStep() {
                    if (this.step > 1) this.step--;
                }
            })),

            Alpine.data('validation', () => ({
                    fechaLimite: '',
                    mascara: null,
                    timeoutValidacion: null,
                    errorMostrado: null, 

                    // init() se ejecuta automáticamente cuando el componente se carga
                    init() {
                        const hoy = new Date();
                        const anioMinimo = hoy.getFullYear() - 17;
                        const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                        const dia = String(hoy.getDate()).padStart(2, '0');
                        // Guardamos el resultado en la propiedad reactiva
                        this.fechaLimite = `${anioMinimo}-${mes}-${dia}`;

                        // actualizacion de regex
                        const tipoDocumento = document.getElementById('id_tipo_documento');
                        const documento = document.getElementById('documento_identidad');

                        const actualizar = () => {
                            const opcion = tipoDocumento.options[tipoDocumento.selectedIndex];
                            const regex  = opcion?.dataset.regex;
                            const place  = opcion?.dataset.place;

                            if (regex) {
                                documento.setAttribute('pattern', regex);
                                documento.setAttribute('placeholder', place);
                                this.mascara = this.compilarMascara(regex);
                            } else {
                                documento.removeAttribute('pattern');
                                documento.removeAttribute('placeholder');
                                this.mascara = null;
                            }
                            documento.value = '';
                            documento.classList.remove('is-invalid');
                            this.errorMostrado = null;
                        };

                        tipoDocumento.addEventListener('change', actualizar);
                        actualizar();
                    },

                    aplicarMascara(event) {
                        const input = event.target;
                        let valor = input.value.replace(/\D/g, "");
                        if (valor.length > 4) {
                            valor = valor.substring(0, 4) + "-" + valor.substring(4, 8);
                        }
                        input.value = valor;
                    },

                    compilarMascara(regexStr) {
                        let p = regexStr.replace(/^\^/, '').replace(/\$$/, '');
                        const tokens = [];
                        let i = 0;

                        while (i < p.length) {
                            const ch = p[i];
                            let test = null;

                            if (ch === '\\' && p[i + 1] === 'd') {
                                test = c => /\d/.test(c);
                                i += 2;
                            } else if (ch === '[') {
                                const fin = p.indexOf(']', i);
                                const re  = new RegExp(`^[${p.slice(i + 1, fin)}]$`);
                                test = c => re.test(c);
                                i = fin + 1;
                            } else if (ch === '(') {
                                // Grupo opcional tipo (?:No\s+)? -> se ignora para la máscara
                                const fin = p.indexOf(')', i);
                                i = fin + 1;
                                if (p[i] === '?') i++;
                                continue;
                            } else {
                                tokens.push({ literal: ch });
                                i++;
                                continue;
                            }

                            // Cuantificador {min} o {min,max}
                            let min = 1, max = 1;
                            if (p[i] === '{') {
                                const fin = p.indexOf('}', i);
                                const partes = p.slice(i + 1, fin).split(',');
                                min = parseInt(partes[0], 10);
                                max = partes[1] ? parseInt(partes[1], 10) : min;
                                i = fin + 1;
                            }

                            for (let k = 0; k < max; k++) {
                                tokens.push({ test, optional: k >= min });
                            }
                        }
                        return tokens;
                    },

                    /* Formatea el valor crudo según los tokens de la máscara */
                    formatoConMascara(valor, tokens) {
                        let resultado = '';
                        let vi = 0;

                        for (const tok of tokens) {
                            if (vi >= valor.length) break;

                            if (tok.literal) {
                                if (valor[vi] === tok.literal) vi++;
                                resultado += tok.literal;
                            } else {
                                while (vi < valor.length && !tok.test(valor[vi])) vi++;
                                if (vi < valor.length) {
                                    resultado += valor[vi];
                                    vi++;
                                }
                            }
                        }
                        return resultado;
                    },

                    mascarasRequex(event) {
                        if (this.mascara) {
                            const input = event.target;
                            input.value = this.formatoConMascara(input.value, this.mascara);
                        }                       

                        clearTimeout(this.timeoutValidacion);
                        this.timeoutValidacion = setTimeout(() => this.validarDocumento(false), 600);
                    },

                    esPrefijoValido(valor, tokens) {
                        if (!tokens || valor.length > tokens.length) return false;

                        for (let i = 0; i < valor.length; i++) {
                            const tok = tokens[i];
                            if (tok.literal) {
                                if (valor[i] !== tok.literal) return false;
                            } else if (!tok.test(valor[i])) {
                                return false;
                            }
                        }
                        return valor.length < tokens.length;
                    },

                   
                validarDocumento(alBlur = false) {

                    const tipoDocumento = document.getElementById('id_tipo_documento');
                    const documento = document.getElementById('documento_identidad');
                    const hint = document.getElementById('documento_hint');
                    const opcion = tipoDocumento.options[ tipoDocumento.selectedIndex ];
                    const regex = opcion?.dataset.regex;
                    const formato = opcion?.dataset.place;
                    const codigo = opcion?.dataset.codigo;
                    const nombre = opcion?.dataset.name;
                    const valor = documento.value.trim();


                    // =====================================================
                    // CAMPO VACÍO
                    // =====================================================

                    if (!regex || valor === '') {

                        documento.setCustomValidity('');

                        documento.classList.remove('is-invalid');

                        if (hint) {
                            hint.textContent = '';
                        }

                        this.errorMostrado = null;

                        return;
                    }


                    let tipoError = null;


                    // =====================================================
                    // 1. PREFIJO VÁLIDO / DOCUMENTO INCOMPLETO
                    // =====================================================

                    const prefijoValido = this.esPrefijoValido( valor, this.mascara );

                    if (prefijoValido) {

                        /*
                        * Mientras escribe:
                        * No mostramos error visual.
                        *
                        * PERO el campo sigue siendo inválido
                        * para impedir que nextStep() avance.
                        */

                        documento.setCustomValidity(`El documento debe tener el formato ${formato}.`);


                        if (!alBlur) {

                            documento.classList.remove('is-invalid');

                            if (hint) {
                                hint.textContent = `Formato: ${formato}`;
                            }

                            this.errorMostrado = null;

                            return;
                        }


                        /*
                        * Si salió del campo:
                        * mostramos el error.
                        */

                        tipoError = 'incompleto';
                    }


                    // =====================================================
                    // 2. VALIDACIÓN DEL FORMATO COMPLETO
                    // =====================================================

                    if (!tipoError) {

                        const re = new RegExp(`^(?:${regex})$`);

                        const formatoOk = re.test(valor);


                        // =================================================
                        // FORMATO INCORRECTO
                        // =================================================
                        if (codigo === 'PORT' && VALIDACION_EXTRA.PORT ) {  
                                if (!VALIDACION_EXTRA.PORT(valor)) {
                                    tipoError = 'pasaporte';
                                } else {
                                    tipoError = null;
                                }
                        }

                        // =================================================
                        // FORMATO CORRECTO
                        // =================================================

                        else if (codigo === 'DUI') {
                            
                            if (!formatoOk) {
                                tipoError = 'formato';
                            }
                            else if (
                                VALIDACION_EXTRA.DUI &&
                                !VALIDACION_EXTRA.DUI(valor)
                            ) {

                                tipoError = 'checksum';
                            }
                        } else {

                            if (!formatoOk) {

                                tipoError = 'formato';
                            }
                        }
                    }


                    // =====================================================
                    // 3. ESTADO REAL DEL CAMPO
                    // =====================================================

                    if (tipoError !== null) {

                        /*
                        * Esto es lo que hará que:
                        *
                        * input.checkValidity()
                        *
                        * devuelva FALSE.
                        */

                        let mensajeValidacion = '';

                        switch (tipoError) {

                            case 'incompleto':
                                mensajeValidacion =
                                    `El documento debe tener el formato ${formato}.`;
                                break;

                            case 'formato':
                                mensajeValidacion =
                                    `El ${nombre} debe cumplir el patrón ${formato}.`;
                                break;

                            case 'checksum':
                                mensajeValidacion =
                                    'El DUI no es válido. Verifique que el número sea correcto.';
                                break;

                            case 'pasaporte':
                                mensajeValidacion =
                                    'El pasaporte debe comenzar con una letra seguida de 8 dígitos.';
                                break;
                        }

                        documento.setCustomValidity(
                            mensajeValidacion
                        );

                    } else {
                        documento.setCustomValidity('');
                    }


                    // =====================================================
                    // 4. ESTADO VISUAL BOOTSTRAP
                    // =====================================================

                    documento.classList.toggle(
                        'is-invalid',
                        tipoError !== null
                    );


                    // =====================================================
                    // 5. MENSAJES TOAST
                    // =====================================================

                    if (
                        tipoError &&
                        this.errorMostrado !== tipoError
                    ) {

                        const mensajes = {

                            incompleto: {
                                title: 'Documento incompleto',
                                msg:
                                    `El documento debe tener el formato ${formato}.`
                            },

                            formato: {
                                title: 'Formato inválido',
                                msg:
                                    `El ${nombre} debe cumplir el patrón ${formato}.`
                            },

                            checksum: {
                                title: 'Número de documento inválido',
                                msg:
                                    'El DUI no es válido. Verifique que el número sea correcto.'
                            },

                            pasaporte: {
                                title: 'Pasaporte inválido',
                                msg:
                                    'El pasaporte debe comenzar con una letra seguida de 8 dígitos.'
                            }
                        };


                        const mensaje = mensajes[tipoError];


                        if (mensaje) {
                            NexumToast.warning(
                                mensaje.msg,
                                mensaje.title
                            );
                        }
                    }


                    // =====================================================
                    // 6. GUARDAR ÚLTIMO ERROR
                    // =====================================================

                    this.errorMostrado =
                        tipoError;


                    // =====================================================
                    // 7. HINT
                    // =====================================================

                    if (hint) {

                        switch (tipoError) {

                            case 'incompleto':
                            case 'formato':

                                hint.textContent =
                                    `Debe tener el formato ${formato}`;

                                break;


                            case 'checksum':

                                hint.textContent =
                                    'El número de DUI no es válido.';

                                break;


                            case 'pasaporte':

                                hint.textContent =
                                    'Debe tener una letra seguida de 8 dígitos.';

                                break;


                            default:

                                hint.textContent =
                                    `Formato: ${formato}`;

                                break;
                        }
                    }
                },
            }));
        });
    </script>
    <x-toast-container />
</body>
</html>