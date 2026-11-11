<?php

namespace App\Rules\Egreso;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Academico\ConfiguracionAcademica;
use App\Models\Estudiante\Alumnos;

class HorasSocialesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el alumno desde la ruta para conocer su carrera
        $alumnoId = (int) $this->route('alumnoId');
        $alumno   = Alumnos::find($alumnoId);

        $max = 2000;
        if ($alumno) {
            $config = ConfiguracionAcademica::paraCarrera($alumno->id_carrera);
            $max    = (int) $config->uv_requeridas_horas_sociales;
        }

        return [
            'horas_sociales_completadas' => ['required', 'integer', 'min:0', "max:{$max}"],
            'horas_sociales_aprobadas'   => ['required', 'boolean'],
            'documento'                  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'horas_sociales_completadas.required' => 'Debes ingresar las horas completadas.',
            'horas_sociales_completadas.integer'  => 'Las horas deben ser un número entero.',
            'horas_sociales_completadas.max'      => 'Has superado el límite de horas permitido para tu carrera.',
            'documento.mimes'                     => 'El documento debe ser PDF, JPG o PNG.',
            'documento.max'                       => 'El archivo no puede exceder 5 MB.',
        ];
    }
}