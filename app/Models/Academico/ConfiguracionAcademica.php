<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionAcademica extends Model
{
    protected $table = 'configuracion_academica';

    protected $primaryKey = 'config_id';

    public $timestamps = false;

    protected $fillable = [
        'id_carrera',
        'nota_minima_aprobatoria',
        'max_reprobaciones_materia',
        'cum_minimo_materia_extra',
        'uv_requeridas_horas_sociales',
        'uv_totales_egreso',
    ];

    protected $attributes = [
        'nota_minima_aprobatoria' => 6.00,
        'max_reprobaciones_materia' => 3,
        'cum_minimo_materia_extra' => 8.50,
        'uv_requeridas_horas_sociales' => 80,
        'uv_totales_egreso' => 160,
    ];

    protected function casts(): array
    {
        return [
            'config_id' => 'integer',
            'id_carrera' => 'integer',
            'nota_minima_aprobatoria' => 'decimal:2',
            'max_reprobaciones_materia' => 'integer',
            'cum_minimo_materia_extra' => 'decimal:2',
            'uv_requeridas_horas_sociales' => 'integer',
            'uv_totales_egreso' => 'integer',
        ];
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'carrera_id');
    }

    public static function paraCarrera(int $idCarrera): self
    {
        return self::firstOrNew(
            ['id_carrera' => $idCarrera],
            [
                'nota_minima_aprobatoria' => 6.00,
                'max_reprobaciones_materia' => 3,
                'cum_minimo_materia_extra' => 8.50,
                'uv_requeridas_horas_sociales' => 80,
                'uv_totales_egreso' => 160,
            ]
        );
    }
}
