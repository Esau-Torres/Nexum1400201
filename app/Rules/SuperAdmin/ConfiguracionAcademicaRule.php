<?php

namespace App\Rules\SuperAdmin;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Users\Role;

class ConfiguracionAcademicaRule implements Rule
{
    private string $mensaje = '';

    public function __construct(
        private string $campo,
        private mixed $valor
    ) {}

    public function authorize(): bool
    {
        return $this->user()->hasRole(Role::SUPER_ADMIN);
    }

    public function passes($attribute, $value): bool
    {
        switch ($this->campo) {
            case 'nota_minima_aprobatoria':
                if (!is_numeric($value) || $value < 0 || $value > 10) {
                    $this->mensaje = 'La nota mínima debe estar entre 0 y 10.';
                    return false;
                }
                break;

            case 'max_reprobaciones_materia':
                if (!is_int($value) || $value < 1 || $value > 10) {
                    $this->mensaje = 'El máximo de reprobaciones debe estar entre 1 y 10.';
                    return false;
                }
                break;

            case 'cum_minimo_materia_extra':
                if (!is_numeric($value) || $value < 0 || $value > 10) {
                    $this->mensaje = 'El CUM mínimo debe estar entre 0 y 10.';
                    return false;
                }
                break;

            case 'uv_requeridas_horas_sociales':
                if (!is_int($value) || $value < 0 || $value > 1000) {
                    $this->mensaje = 'Las UV de horas sociales deben ser un entero entre 0 y 1000.';
                    return false;
                }
                break;

            case 'uv_totales_egreso':
                if (!is_int($value) || $value < 100) {
                    $this->mensaje = 'Las UV totales de egreso deben ser al menos 100.';
                    return false;
                }
                break;
        }

        // Validación cruzada
        if ($this->campo === 'uv_totales_egreso' && isset($this->valor['uv_requeridas_horas_sociales'])) {
            if ($value <= $this->valor['uv_requeridas_horas_sociales']) {
                $this->mensaje = 'Las UV totales deben ser mayores a las UV de horas sociales.';
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return $this->mensaje;
    }
}