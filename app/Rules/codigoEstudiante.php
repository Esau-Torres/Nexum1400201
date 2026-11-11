<?php

namespace App\Rules;

class CodigoEstudiante {
    public static function generar(string $nombreCompleto, int $userId): string
    {
        // genera un código de estudiante basado en las iniciales del apellido, el año actual y un número correlativo
        $palabras = array_values(array_filter(explode(' ', trim($nombreCompleto))));
        
        $apellido1 = $palabras[count($palabras) - 2] ?? $palabras[0] ?? 'X';
        $apellido2 = $palabras[count($palabras) - 1] ?? 'X';
        
        $iniciales = strtoupper(substr($apellido1, 0, 1) . substr($apellido2, 0, 1));
        $anio = date('Y');
        $correlativo = str_pad((string)$userId, 5, '0', STR_PAD_LEFT);

        return $iniciales.$anio.$correlativo;
    }
}