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

                        if (!tipoDocumento || !documento) {
                            // No hay select de tipo documento en esta vista → no hay nada más que hacer
                            return;
                        }

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
};