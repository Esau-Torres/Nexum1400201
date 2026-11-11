<?php

namespace App\Enums;

use App\Enums\TamanoGrupo;
use App\Models\Academico\ProcesoGraduacion;
use Illuminate\Support\Facades\DB;

class ArancelesGraduacion
{
    public static function aperturaExpediente(string $codigoNivel): string
    {
        return $codigoNivel === 'MAESTRIA' ? 'MAE-001' : 'GRA-001';
    }

     /**
     * Arancel de entrega/revisión de anteproyecto según nivel y tamaño.
     */
    public static function revisionAnteproyecto(string $nivel, TamanoGrupo $tamano): string
    {
        if ($nivel === 'MAESTRIA') {
            return match ($tamano) {
                TamanoGrupo::INDIVIDUAL => 'MAE-005',
                TamanoGrupo::PAREJA     => 'MAE-006',
                TamanoGrupo::GRUPO_TRES => 'MAE-007',
            };
        }

        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'TES-002',
            TamanoGrupo::PAREJA     => 'TES-003',
            TamanoGrupo::GRUPO_TRES => 'TES-004',
        };
    }

    /**
     * Arancel de revisión de tesis según nivel y tamaño.
     */
    public static function revisionTesis(string $nivel, TamanoGrupo $tamano): string
    {
        if ($nivel === 'MAESTRIA') {
            return match ($tamano) {
                TamanoGrupo::INDIVIDUAL => 'MAE-008',
                TamanoGrupo::PAREJA     => 'MAE-009',
                TamanoGrupo::GRUPO_TRES => 'MAE-010',
            };
        }

        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'TES-005',
            TamanoGrupo::PAREJA     => 'TES-006',
            TamanoGrupo::GRUPO_TRES => 'TES-007',
        };
    }

    /**
     * Arancel de defensa según nivel y tamaño.
     */
    public static function defensa(string $nivel, TamanoGrupo $tamano): string
    {
        if ($nivel === 'MAESTRIA') {
            return match ($tamano) {
                TamanoGrupo::INDIVIDUAL => 'MAE-011',
                TamanoGrupo::PAREJA     => 'MAE-012',
                TamanoGrupo::GRUPO_TRES => 'MAE-013',
            };
        }

        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'TES-008',
            TamanoGrupo::PAREJA     => 'TES-009',
            TamanoGrupo::GRUPO_TRES => 'TES-010',
        };
    }

     /**
     * Arancel de cuotas de asesoría (12 pagos mensuales).
     */
    public static function cuotasAsesoria(string $nivel, TamanoGrupo $tamano): string
    {
        if ($nivel === 'MAESTRIA') {
            return match ($tamano) {
                TamanoGrupo::INDIVIDUAL => 'MAE-002',
                TamanoGrupo::PAREJA     => 'MAE-003',
                TamanoGrupo::GRUPO_TRES => 'MAE-004',
            };
        }

        return 'TES-001';
    }

    /**
     * Aranceles de asesoría según tamaño del grupo (pregrado).
     */
    public static function asesoriaPregrado(TamanoGrupo $tamano): string
    {
        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'TES-001', // 12 cuotas mensuales
            TamanoGrupo::PAREJA     => 'TES-003', // Anteproyecto 2 estudiantes
            TamanoGrupo::GRUPO_TRES => 'TES-004', // Anteproyecto 3 estudiantes
        };
    }

    /**
     * Aranceles de asesoría según tamaño del grupo (maestría).
     */
    public static function asesoriaMaestria(TamanoGrupo $tamano): string
    {
        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'MAE-002', // 12 cuotas, 1 estudiante
            TamanoGrupo::PAREJA     => 'MAE-003', // 12 cuotas, 2 estudiantes
            TamanoGrupo::GRUPO_TRES => 'MAE-004', // 12 cuotas, 3 estudiantes
        };
    }

    /**
     * Aranceles de defensa según tamaño del grupo (pregrado).
     */
    public static function defensaPregrado(TamanoGrupo $tamano): string
    {
        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'TES-008',
            TamanoGrupo::PAREJA     => 'TES-009',
            TamanoGrupo::GRUPO_TRES => 'TES-010',
        };
    }

    /**
     * Aranceles de defensa según tamaño del grupo (maestría).
     */
    public static function defensaMaestria(TamanoGrupo $tamano): string
    {
        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'MAE-011',
            TamanoGrupo::PAREJA     => 'MAE-012',
            TamanoGrupo::GRUPO_TRES => 'MAE-013',
        };
    }

    /**
     * Aranceles de derechos de grado.
     */
    public static function derechosGrado(string $nivel, string $modalidad): string
    {
        if ($modalidad === 'PRE_ESPECIALIZACION') {
            return 'PRE-003';
        }

        return $nivel === 'MAESTRIA' ? 'MAE-014' : 'TES-011';
    }

    /**
     * Aranceles de pre-especialización (siempre individual).
     */
    public static function preEspecializacion(): array
    {
        return [
            'matricula'   => 'PRE-001',
            'mensualidad' => 'PRE-002',
            'derechos'    => 'PRE-003',
        ];
    }

    /**
     * Devuelve todos los aranceles requeridos según nivel, modalidad y tamaño.
     */
    public static function requeridos(
        string $nivel,
        string $modalidad,
        ?TamanoGrupo $tamano = null
    ): array {
        if ($modalidad === 'PRE_ESPECIALIZACION') {
            return self::preEspecializacion();
        }

        // TRABAJO_GRADUACION
        $tamano = $tamano ?? TamanoGrupo::INDIVIDUAL;

        if ($nivel === 'MAESTRIA') {
            return [
                'apertura'    => 'MAE-001',
                'asesoria'    => self::asesoriaMaestria($tamano),
                'defensa'     => self::defensaMaestria($tamano),
                'derechos'    => 'MAE-014',
            ];
        }

        return [
            'apertura'    => 'GRA-001',
            'asesoria'    => self::asesoriaPregrado($tamano),
            'defensa'     => self::defensaPregrado($tamano),
            'derechos'    => 'TES-011',
        ];
    }

    /**
     * Filtra los pagos a partir de la fecha de creación del proceso.
     * Si el proceso es null (creación), usa hoy - 1 año como fecha mínima.
     */
    public static function queryPagosDesde(ProcesoGraduacion $proceso): \Illuminate\Database\Query\Builder
    {
        $query = DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id');

        if ($proceso && $proceso->created_at) {
            $query->where('p.fecha_pago', '>=', $proceso->created_at);
        } else {
            $query->where('p.fecha_pago', '>=', now()->subYear());
        }

        return $query;
    }
}