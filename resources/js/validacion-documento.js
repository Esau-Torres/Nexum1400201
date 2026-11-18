// validacion del formato del dui
export function checksumDUI(valor) {
    const limpio = valor.replace(/-/g, '').trim();
    if (limpio.length !== 9 || !/^\d+$/.test(limpio)) return false;

    let suma = 0;
    for (let i = 0; i < 8; i++) {
        suma += parseInt(limpio[i], 10) * (9 - i);
    }
    return (10 - (suma % 10)) % 10 === parseInt(limpio[8], 10);
}

        // validacion extra del pasaporte en tiempo real 
export function validarPasaporte(valor) {
    const limpio = valor.trim();
    return /^[A-Za-z]\d{8}$/.test(limpio);
}

// Clave = tipo_documento_id (DUI = DUI, PORT = Pasaporte, CDN = Minoridad)
export const VALIDACION_EXTRA = {
    'DUI': checksumDUI,
    'PORT': validarPasaporte,
};

export function registrarComponentesValidacion(Alpine) {
            Alpine.data('registroStepper', () => ({
                
                step: 1,
                titles: ['Datos Personales', 'Academia', 'Documentos'],
                
                getStepTitle(index) {
                    return this.titles[index - 1];
                },
                
                nextStep() {
                    // Selecciona únicamente el contenedor del paso activo actual
                    const currentStepEl = document.querySelector(`div[x-show="step === ${this.step}"]`);
                    if (!currentStepEl) return;

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
            }));

            Alpine.data('validation', () => ({
                    fechaLimite: '',
                    fechaLimiteAntiguedad: '', // 90 años
                    mascara: null,
                    timeoutValidacion: null,
                    errorMostrado: null,  
                    timeoutEmail: null,
                    emailDisponible: true,
                    emailVerificado: '', 
                    formRevision: 0,
                    intentoEnvio: false,
                    checkEmailUrl: '',

                    // init() se ejecuta automáticamente cuando el componente se carga
                    init() {
                        const hoy = new Date();
                        const anioMinimo = hoy.getFullYear() - 17;
                        const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                        const dia = String(hoy.getDate()).padStart(2, '0');
                        const anioMaxAntiguedad = hoy.getFullYear() - 90;

                        this.fechaLimite = `${anioMinimo}-${mes}-${dia}`;
                        this.fechaLimiteAntiguedad = `${anioMaxAntiguedad}-${mes}-${dia}`;

                        // === Resolver la URL del endpoint de email (funciona en create Y modal) ===
                        // this.$el puede SER el <form> (modal) o estar DENTRO del <form> (create)
                        const formAncestro = this.$el.tagName === 'FORM'
                            ? this.$el
                            : this.$el.closest('form');

                        this.checkEmailUrl = formAncestro?.dataset.checkEmailUrl ?? '';
                        this.ignoreUserId  = formAncestro?.dataset.userId ?? '';

                        // === Intento A: create (hay select de tipo documento) ===
                        const tipoDocumento = document.getElementById('id_tipo_documento');
                        const documentoId   = document.getElementById('documento_identidad');

                        if (tipoDocumento && documentoId) {
                            const actualizar = () => {
                                const opcion = tipoDocumento.options[tipoDocumento.selectedIndex];
                                const regex  = opcion?.dataset.regex;
                                const place  = opcion?.dataset.place;

                                if (regex) {
                                    documentoId.setAttribute('pattern', regex);
                                    documentoId.setAttribute('placeholder', place);
                                    this.mascara = this.compilarMascara(regex);
                                } else {
                                    documentoId.removeAttribute('pattern');
                                    documentoId.removeAttribute('placeholder');
                                    this.mascara = null;
                                }
                                documentoId.value = '';
                                documentoId.classList.remove('is-invalid');
                                this.errorMostrado = null;
                            };

                            tipoDocumento.addEventListener('change', actualizar);
                            actualizar();
                            return; // listo para create
                        }

                        // === Intento B: modal de edición (no hay select, pero hay x-ref="documentoInput") ===
                        const documentoModal = this.$refs.documentoInput;
                        if (documentoModal) {
                            const regex = documentoModal.dataset.regex;
                            const place = documentoModal.dataset.place;

                            if (regex) {
                                documentoModal.setAttribute('pattern', regex);
                                if (place) documentoModal.setAttribute('placeholder', place);
                                this.mascara = this.compilarMascara(regex);
                            } else {
                                this.mascara = null;
                            }
                            // NO limpiamos el valor: en edición debe conservarse el actual
                            documentoModal.classList.remove('is-invalid');
                            this.errorMostrado = null;
                        }
                    },

                    // validacion de Nombre
                    validarNombre(event, alBlur = false) {
                        const input = event.target;
                        const valorOriginal = input.value;

                        // Elimina caracteres no permitidos en tiempo real
                        const limpio = valorOriginal
                            .replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')  // quita números y especiales
                            .replace(/\s{2,}/g, ' ')                      // colapsa espacios múltiples
                            .replace(/^\s+/, '');                         // sin espacio al inicio

                        if (limpio !== valorOriginal) {
                            input.value = limpio;
                        }

                        const valor = input.value.trim();
                        const feedback = this._feedbackDe(input);

                        let error = '';

                        if (valor === '') {
                            error = 'El nombre completo es obligatorio.';
                        } else if (valor.length < 3) {
                            error = 'El nombre debe tener al menos 3 caracteres.';
                        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]+(?:\s+[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]+)*$/.test(valor)) {
                            error = 'Solo se permiten letras y espacios (sin números ni caracteres especiales).';
                        }

                        if (error) {
                            input.setCustomValidity(error);
                            // Solo mostramos el error visual al salir del campo o si ya hay texto inválido claro
                            if (alBlur || valor.length > 0) {
                                input.classList.add('is-invalid');
                                if (feedback) feedback.textContent = error;
                            }
                        } else {
                            input.setCustomValidity('');
                            input.classList.remove('is-invalid');
                            if (feedback) feedback.textContent = '';
                        }
                    },

                    /* =========================================================
                    *  VALIDACIÓN DE EMAIL (formato + disponibilidad)
                    * ========================================================= */
                    async validarEmail(event, alBlur = false) {
                        const input = event.target;

                        const posicionCursor = input.selectionStart;
                        const original = input.value;
                        const enMinusculas = original.toLowerCase();

                        if (original !== enMinusculas) {
                            input.value = enMinusculas;
                            // Restaurar la posición del cursor (evita saltos al final del input)
                            try {
                                input.setSelectionRange(posicionCursor, posicionCursor);
                            } catch (_) { /* algunos navegadores no lo permiten en type=email */ }
                        }


                        const valor = input.value.trim().toLowerCase();
                        const feedback = this._feedbackDe(input);

                        // Regex robusto: exige TLD de 2+ letras y sin puntos al inicio/final ni dobles puntos
                        const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/;

                        let error = '';

                        if (valor === '') {
                            error = 'El correo electrónico es obligatorio.';
                        } else if (!regexEmail.test(valor)) {
                            error = 'Ingrese un correo electrónico válido (ejemplo@dominio.com).';
                        } else if (valor.includes('..') || valor.startsWith('.') || valor.split('@')[0].endsWith('.')) {
                            error = 'El correo contiene puntos consecutivos o mal ubicados.';
                        }

                        if (error) {
                            input.setCustomValidity(error);
                            if (alBlur || valor.length > 0) {
                                input.classList.add('is-invalid');
                                if (feedback) feedback.textContent = error;
                            }
                            this.emailDisponible = false;
                            return;
                        }

                        // Formato correcto → verificar unicidad con debounce
                        input.setCustomValidity('');
                        this.emailDisponible = false; // bloquea temporalmente mientras consulta
                        if (feedback) feedback.textContent = 'Verificando disponibilidad...';
                        input.classList.remove('is-invalid');

                        clearTimeout(this.timeoutEmail);
                        this.timeoutEmail = setTimeout(async () => {
                            if (input.value.trim().toLowerCase() !== valor) return;

                            try {
                                // NUEVO: construye query con ignore_user_id si existe
                                const params = new URLSearchParams({ email: valor });
                                if (this.ignoreUserId) params.append('ignore_user_id', this.ignoreUserId);

                                // Guard defensivo: si no hay URL, no pegamos a la ruta actual
                                if (!this.checkEmailUrl) throw new Error('checkEmailUrl vacío');

                                const res = await fetch(
                                    `${this.checkEmailUrl}?${params.toString()}`,
                                    { headers: { 'Accept': 'application/json' } }
                                );

                                if (!res.ok) throw new Error('HTTP ' + res.status);

                                const data = await res.json();

                                if (data.available === true) {
                                    input.setCustomValidity('');
                                    input.classList.remove('is-invalid');
                                    if (feedback) feedback.textContent = '';
                                    this.emailDisponible = true;
                                } else {
                                    input.setCustomValidity('Este correo ya está registrado.');
                                    input.classList.add('is-invalid');
                                    if (feedback) feedback.textContent = 'Este correo ya está registrado.';
                                    this.emailDisponible = false;
                                }
                            } catch (e) {
                                input.setCustomValidity('No se pudo verificar el correo. Intente nuevamente.');
                                input.classList.add('is-invalid');
                                if (feedback) feedback.textContent = 'No se pudo verificar el correo. Intente nuevamente.';
                                this.emailDisponible = false;
                            }
                        }, 400);
                    },

                    /* =========================================================
                    *  VALIDACIÓN DE FECHA DE NACIMIENTO (rango 90–17 años)
                    * ========================================================= */
                    validarFechaNacimiento(event) {
                        const input = event.target;
                        const feedback = this._feedbackDe(input);
                        const valor = input.value;

                        if (!valor) {
                            input.setCustomValidity('La fecha de nacimiento es obligatoria.');
                            return;
                        }

                        const nacimiento = new Date(valor + 'T00:00:00');
                        const hoy = new Date();

                        let edad = hoy.getFullYear() - nacimiento.getFullYear();
                        const m = hoy.getMonth() - nacimiento.getMonth();
                        if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
                            edad--;
                        }

                        let error = '';

                        if (edad < 17) {
                            error = 'El usuario debe ser mayor de 17 años.';
                        } else if (edad > 90) {
                            error = 'La fecha de nacimiento no puede superar los 90 años de antigüedad.';
                        } else if (nacimiento > hoy) {
                            error = 'La fecha de nacimiento no puede ser futura.';
                        }

                        if (error) {
                            input.setCustomValidity(error);
                            input.classList.add('is-invalid');
                            if (feedback) feedback.textContent = error;
                        } else {
                            input.setCustomValidity('');
                            input.classList.remove('is-invalid');
                            if (feedback) feedback.textContent = '';
                        }
                    },

                    tocarFormulario() {
                        this.formRevision++;
                    },

                     // validez global del formulario en tiempo real 
                    get formularioValido() {
                        void this.formRevision;

                        const form = this.$refs.formulario;
                        if (!form) return false;

                        if (!form.checkValidity()) return false;

                        const rolesSeleccionados = document.querySelectorAll('.rol-check:checked');
                        if (rolesSeleccionados.length === 0) return false;

                        // 3. Email verificado contra BD
                        if (!this.emailDisponible) return false;

                        return true;
                    },

                    /* =========================================================
                    *  VALIDACIÓN DE OBSERVACIONES
                    * ========================================================= */
                    validarObservaciones(event, alBlur = false) {
                        const input = event.target;
                        const feedback = this._feedbackDe(input);
                        const valorOriginal = input.value;

                        //    ni espacio ni salto de línea.
                        const limpio = valorOriginal
                            .replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')  // quita números y especiales
                            .replace(/[ \t]{2,}/g, ' ')                  // colapsa espacios/tabs múltiples
                            .replace(/\n{3,}/g, '\n\n')                  // máximo 2 saltos seguidos
                            .replace(/^\s+/, '');                        // sin espacio al inicio

                        if (limpio !== valorOriginal) {
                            input.value = limpio;
                        }

                        const valor = input.value.trim();

                        // 2) Reglas de longitud + regex estricto de verificación
                        let error = '';

                        if (valor === '') {
                            error = 'Las observaciones son obligatorias.';
                        } else if (valor.length < 5) {
                            error = 'Las observaciones deben tener al menos 5 caracteres.';
                        } else if (valor.length > 500) {
                            error = 'Las observaciones no pueden superar los 500 caracteres.';
                        } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]+(?:[\s]+[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]+)*$/.test(valor)) {
                            error = 'Solo se permiten letras y espacios (sin números ni caracteres especiales).';
                        }

                        // 3) Aplicar estado (customValidity + clases)
                        if (error) {
                            input.setCustomValidity(error);
                            if (alBlur || valor.length > 0) {
                                input.classList.add('is-invalid');
                                if (feedback) feedback.textContent = error;
                            }
                        } else {
                            input.setCustomValidity('');
                            input.classList.remove('is-invalid');
                            if (feedback) feedback.textContent = '';
                        }
                    },

                    /* =========================================================
                    *  VALIDACIÓN DE CÓDIGO DE MATERIA
                    * ========================================================= */
                    validarCodigoMateria(event, alBlur = false) {
                        const input = event.target;
                        const valorOriginal = input.value;

                        // Limpieza en vivo: mayúsculas + solo A-Z 0-9 -
                        const limpio = valorOriginal
                            .toUpperCase()
                            .replace(/[^A-Z0-9]/g, '')
                            .slice(0, 20);

                        if (limpio !== valorOriginal) {
                            input.value = limpio;
                        }

                        const valor = input.value.trim();
                        const feedback = this._feedbackDe(input);

                        let error = '';

                        if (valor === '') {
                            error = 'El código es obligatorio.';
                        } else if (valor.length < 3) {
                            error = 'El código debe tener al menos 3 caracteres.';
                        } else if (!/^[A-Z0-9]{3,20}$/.test(valor)) {
                            error = 'Solo se permiten letras mayúsculas, números y guiones (3 a 20 caracteres).';
                        }

                        if (error) {
                            input.setCustomValidity(error);
                            if (alBlur || valor.length > 0) {
                                input.classList.add('is-invalid');
                                if (feedback) feedback.textContent = error;
                            }
                        } else {
                            input.setCustomValidity('');
                            input.classList.remove('is-invalid');
                            if (feedback) feedback.textContent = '';
                        }
                    },

                    _feedbackDe(input) {
                        const contenedor = input.closest('.col-12, .col, [class*="col-"]') || input.parentElement;
                        if (contenedor) {
                            const fb = contenedor.querySelector('.invalid-feedback');
                            if (fb) return fb;
                        }
                        if (input.name) {
                            const fb = document.querySelector(`[data-feedback-for="${input.name}"]`);
                            if (fb) return fb;
                        }
                        return null;
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
                        this.timeoutValidacion = setTimeout(() => this.validarDocumento(event, false), 600);
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

                   
                validarDocumento(event = null, alBlur = false) {
                    // =====================================================
                    // 0. RESOLVER INPUT Y ORIGEN DE DATOS
                    //    - Modal: event.target es el input (no hay <select>)
                    //    - Create: getElementById (sí hay <select>)
                    // =====================================================
                    let documento, tipoDocumento;

                    if (event && event.target) {
                        documento = event.target;
                        tipoDocumento = null;
                    } else {
                        tipoDocumento = document.getElementById('id_tipo_documento');
                        documento     = document.getElementById('documento_identidad');
                    }

                    // Si no hay input, salimos sin romper
                    if (!documento) return;

                    const hint = document.getElementById('documento_hint');

                    // Resolver regex/place/codigo/nombre según el escenario
                    let regex, formato, codigo, nombre;

                    if (tipoDocumento) {
                        const opcion = tipoDocumento.options[tipoDocumento.selectedIndex];
                        regex   = opcion?.dataset.regex;
                        formato = opcion?.dataset.place;
                        codigo  = opcion?.dataset.codigo;
                        nombre  = opcion?.dataset.name;
                    } else {
                        regex   = documento.dataset.regex;
                        formato = documento.dataset.place;
                        codigo  = documento.dataset.codigo;
                        nombre  = documento.dataset.name;
                    }

                    const valor = documento.value.trim();

                    // =====================================================
                    // CAMPO VACÍO
                    // =====================================================
                    if (!regex || valor === '') {
                        documento.setCustomValidity('');
                        documento.classList.remove('is-invalid');
                        if (hint) hint.textContent = '';
                        this.errorMostrado = null;
                        return;
                    }

                    let tipoError = null;

                    // =====================================================
                    // 1. PREFIJO VÁLIDO / DOCUMENTO INCOMPLETO
                    // =====================================================
                    const prefijoValido = this.esPrefijoValido(valor, this.mascara);

                    if (prefijoValido) {
                        documento.setCustomValidity(`El documento debe tener el formato ${formato}.`);

                        if (!alBlur) {
                            documento.classList.remove('is-invalid');
                            if (hint) hint.textContent = `Formato: ${formato}`;
                            this.errorMostrado = null;
                            return;
                        }

                        tipoError = 'incompleto';
                    }

                    // =====================================================
                    // 2. VALIDACIÓN DEL FORMATO COMPLETO
                    // =====================================================
                    if (!tipoError) {
                        const re = new RegExp(`^(?:${regex})$`);
                        const formatoOk = re.test(valor);

                        if (codigo === 'PORT' && VALIDACION_EXTRA.PORT) {
                            if (!VALIDACION_EXTRA.PORT(valor)) {
                                tipoError = 'pasaporte';
                            } else {
                                tipoError = null;
                            }
                        } else if (codigo === 'DUI') {
                            if (!formatoOk) {
                                tipoError = 'formato';
                            } else if (VALIDACION_EXTRA.DUI && !VALIDACION_EXTRA.DUI(valor)) {
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
                        let mensajeValidacion = '';

                        switch (tipoError) {
                            case 'incompleto':
                                mensajeValidacion = `El documento debe tener el formato ${formato}.`;
                                break;
                            case 'formato':
                                mensajeValidacion = `El ${nombre} debe cumplir el patrón ${formato}.`;
                                break;
                            case 'checksum':
                                mensajeValidacion = 'El DUI no es válido. Verifique que el número sea correcto.';
                                break;
                            case 'pasaporte':
                                mensajeValidacion = 'El pasaporte debe comenzar con una letra seguida de 8 dígitos.';
                                break;
                        }

                        documento.setCustomValidity(mensajeValidacion);
                    } else {
                        documento.setCustomValidity('');
                    }

                    // =====================================================
                    // 4. ESTADO VISUAL BOOTSTRAP
                    // =====================================================
                    documento.classList.toggle('is-invalid', tipoError !== null);

                    // =====================================================
                    // 5. MENSAJES TOAST
                    // =====================================================
                    if (tipoError && this.errorMostrado !== tipoError) {
                        const mensajes = {
                            incompleto: {
                                title: 'Documento incompleto',
                                msg: `El documento debe tener el formato ${formato}.`
                            },
                            formato: {
                                title: 'Formato inválido',
                                msg: `El ${nombre} debe cumplir el patrón ${formato}.`
                            },
                            checksum: {
                                title: 'Número de documento inválido',
                                msg: 'El DUI no es válido. Verifique que el número sea correcto.'
                            },
                            pasaporte: {
                                title: 'Pasaporte inválido',
                                msg: 'El pasaporte debe comenzar con una letra seguida de 8 dígitos.'
                            }
                        };

                        const mensaje = mensajes[tipoError];
                        if (mensaje) {
                            NexumToast.warning(mensaje.msg, mensaje.title);
                        }
                    }

                    // =====================================================
                    // 6. GUARDAR ÚLTIMO ERROR
                    // =====================================================
                    this.errorMostrado = tipoError;

                    // =====================================================
                    // 7. HINT
                    // =====================================================
                    if (hint) {
                        switch (tipoError) {
                            case 'incompleto':
                            case 'formato':
                                hint.textContent = `Debe tener el formato ${formato}`;
                                break;
                            case 'checksum':
                                hint.textContent = 'El número de DUI no es válido.';
                                break;
                            case 'pasaporte':
                                hint.textContent = 'Debe tener una letra seguida de 8 dígitos.';
                                break;
                            default:
                                hint.textContent = `Formato: ${formato}`;
                                break;
                        }
                    }
                },
            }));
};