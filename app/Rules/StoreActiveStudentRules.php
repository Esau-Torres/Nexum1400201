<?php

namespace App\Rules;

use App\Models\Users\Role;
use App\Rules\ValidarDocumentoIdentidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TipoBeneficio;
use Illuminate\Validation\Rules\Enum;

class StoreActiveStudentRules extends FormRequest
{
    public function authorize(): bool
    {
        // Doble check: aunque la ruta ya tenga middleware role
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'id_tipo_documento'   => ['required', 'exists:tipo_documento_identidad,tipo_documento_id'],
            'documento_identidad' => [
                'required', 'string', 'max:30',
                new ValidarDocumentoIdentidad($this->input('id_tipo_documento')),
                Rule::unique('users', 'documento_identidad')
                    ->where(fn ($q) => $q->where('id_tipo_documento', $this->input('id_tipo_documento'))),
            ],
            'fecha_nacimiento'    => ['required', 'date', 'before:-17 years'],
            'genero'              => ['required', 'in:M,F'],
            'estado_civil'        => ['nullable', 'string', 'max:20'],
            'celular'             => ['nullable', 'string', 'max:20'],
            'direccion'           => ['nullable', 'string'],
            'id_regional_activo'  => ['required', 'exists:regional_activo,regional_activo_id'],
            'id_carrera'          => ['required', 'exists:carreras,carrera_id'],

            // Rol forzado: solo ESTUDIANTE
            'rol' => ['sometimes', 'string', Rule::in([Role::ESTUDIANTE])],

            // Documentos
            'titulo_bachillerato' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'partida_nacimiento'  => ['required', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'fotografia_personal' => ['required', 'file', 'mimes:jpg,png', 'max:2048'],
            'constancia_paes'     => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'observaciones'       => ['nullable', 'string', 'max:1000'],

            // tipo de beneficio de estudiante
            'id_ciclo_lectivo'    => ['nullable', 'integer', 'exists:ciclo_lectivo,ciclo_lectivo_id', 'required_with:tipo_beneficio'],
            'tipo_beneficio'      => ['nullable', new Enum(TipoBeneficio::class), 'required_with:id_ciclo_lectivo'],
            'nombre_convenio'     => ['nullable', 'string', 'max:150', 'required_with:tipo_beneficio'],
            'porcentaje_estudiante' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:tipo_beneficio,BECA_PARCIAL,CUOTA_ESPECIAL'],
            'porcentaje_universidad' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto_fijo_cuota'       => ['nullable', 'numeric', 'min:0', 'required_if:tipo_beneficio,FRANJA_BECARIA' ],
            'resolucion_academica' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'rol.in' => 'Solo se pueden crear usuarios con rol ESTUDIANTE desde este módulo.',
            'id_ciclo_lectivo.required_with'      => 'Seleccioná el ciclo lectivo para asignar el beneficio.',
            'tipo_beneficio.required_with'        => 'Seleccioná el tipo de beneficio.',
            'nombre_convenio.required_with'       => 'Indicá el nombre del convenio o resolución.',
            'monto_fijo_cuota.required_if'        => 'La Franja Becaria requiere un monto fijo.',
            'porcentaje_estudiante.required_if'   => 'Indicá el porcentaje que paga el estudiante.',
        ];
    }
}