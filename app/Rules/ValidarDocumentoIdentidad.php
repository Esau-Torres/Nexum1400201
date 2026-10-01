<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use App\Models\Users\TipoDocumentoIdentidad;

class ValidarDocumentoIdentidad implements ValidationRule
{
    public function __construct(protected ?int $tipoDocumentoId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         $tipo = TipoDocumentoIdentidad::find($this->tipoDocumentoId);

        if (!$tipo || !preg_match("/^(?:{$tipo->formato_regex})$/", trim($value))) {
            $fail('El número de documento ingresado no es válido para el formato seleccionado.');
            return;
        }

        // Extra por ID de registro (DUI = DUI, PORT = Pasaporte, CDN = Minoridad)
        $esValido = match ($tipo->codigo) {
            'DUI' => $this->validarChecksumDUI($value),
            default => true,
        };

        if (!$esValido) {
            $fail('El dígito verificador del DUI no es válido. Verifique el número ingresado.');
        }
    }

    private function validarChecksumDUI(string $dui): bool
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
}