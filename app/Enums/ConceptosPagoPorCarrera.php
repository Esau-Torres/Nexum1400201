<?php

namespace App\Enums;

use App\Models\Estudiante\Carreras;
use Illuminate\Support\Facades\DB;

class ConceptosPagoPorCarrera
{
    /**
     * Devuelve los códigos de concepto de matrícula y cuota mensual
     * según la carrera (y por ende el nivel académico).
     *
     * @return array{codigo_matricula: string, codigos_cuota: string[]}
     */
    public static function para(int $idCarrera): array
    {
        $carrera = DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $idCarrera)
            ->select('c.carrera_id', 'c.nombre', 'na.codigo')
            ->first();

        if (!$carrera) {
            return self::pregrado();
        }

        // Maestría en Docencia Universitaria
        if ($carrera->codigo === 'MAESTRIA'
            && str_contains(strtolower($carrera->nombre), 'docencia universitaria')) {
            return [
                'codigo_matricula' => 'MAT-006',
                'codigos_cuota'    => ['MAT-007'],
            ];
        }

        // Otras maestrías: Finanzas, Psicología Clínica, Derecho Procesal
        if ($carrera->codigo === 'MAESTRIA') {
            return [
                'codigo_matricula' => 'MAT-008',
                'codigos_cuota'    => ['MAT-009'],
            ];
        }

        // Pregrado: Licenciatura, Profesorado, Técnico
        return self::pregrado();
    }

    private static function pregrado(): array
    {
        return [
            'codigo_matricula' => 'MAT-001',
            'codigos_cuota'    => ['MAT-002', 'MAT-003'], // normal + franja becaria
        ];
    }
}