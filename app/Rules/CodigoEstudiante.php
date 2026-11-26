<?php

namespace App\Rules;

class CodigoEstudiante {
    /**
     * Genera un código de estudiante.
     *
     * @param string   $nombreCompleto
     * @param int      $userId
     * @param int|null $sufijoExpediente  Si se pasa, se agrega como sufijo (para N expedientes del mismo usuario)
     */

    public static function generar(string $nombreCompleto, int $userId, ?int $sufijoExpediente = null): string
    {
        $palabras = array_values(array_filter(explode(' ', trim($nombreCompleto))));

        $apellido1 = $palabras[count($palabras) - 2] ?? $palabras[0] ?? 'X';
        $apellido2 = $palabras[count($palabras) - 1] ?? 'X';

        $iniciales = strtoupper(substr($apellido1, 0, 1) . substr($apellido2, 0, 1));
        $anio = date('Y');
        $correlativo = str_pad((string) $userId, 5, '0', STR_PAD_LEFT);

        // Si es un expediente adicional (cambio de carrera), agregar sufijo
        $sufijo = $sufijoExpediente !== null ? '-' . $sufijoExpediente : '';

        return $iniciales.$anio.$correlativo.$sufijo;
    }
}