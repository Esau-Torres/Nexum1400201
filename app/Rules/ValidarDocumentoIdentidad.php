<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarDocumentoIdentidad implements ValidationRule
{
    public function __construct(protected ?int $tipoDocumentoId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->tipoDocumentoId) {
            $fail('Debe seleccionar un tipo de documento.');
            return;
        }
        
        $esValido = match ((int) $this->tipoDocumentoId) {
            1 => $this->validarDUI($value), // 1 = DUI
            2 => $this->validarPasaporte($value), // 2 = Pasaporte
            4 => $this->validarLicencia($value), // 4 = Licencia
            3 => $this->validarMinoridad($value), // 3 = Carnet de Minoridad
            default => false,
        };

        if (!$esValido) {
            $fail('El número de documento ingresado no es válido para el formato seleccionado.');
        }
    }

    private function validarDUI(string $dui): bool
    {
        $duiLimpio = str_replace('-', '', trim($dui));
        
        if (strlen($duiLimpio) !== 9 || !ctype_digit($duiLimpio)) {
            return false;
        }

        $suma = 0;
        for ($i = 0; $i < 8; $i++) {
            $suma += (int)$duiLimpio[$i] * (9 - $i);
        }

        $residuo = $suma % 10;
        $digitoCalculado = (10 - $residuo) % 10;
        $digitoReal = (int)$duiLimpio[8];

        return $digitoCalculado === $digitoReal;
    }

    private function validarPasaporte(string $pasaporte): bool
    {
        // Formato estándar pasaporte salvadoreño (Letra seguida de números)
        return preg_match('/^[A-Z0-9]{6,15}$/i', trim($pasaporte));
    }

    private function validarLicencia(string $licencia): bool
    {
        // Usualmente la licencia en El Salvador homologa con el NIT o DUI
        return preg_match('/^[0-9]{4}-[0-9]{6}-[0-9]{3}-[0-9]{1}$/', trim($licencia)) || $this->validarDUI($licencia);
    }

    private function validarMinoridad(string $carnet): bool
    {
        return preg_match('/^[A-Z0-9-]{7,15}$/i', trim($carnet));
    }
}