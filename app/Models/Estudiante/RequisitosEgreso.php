<?php

namespace App\Models\Estudiante;

use App\Enums\EstadoEgreso;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitosEgreso extends Model
{
    protected $table      = 'requisitos_egreso';
    protected $primaryKey = 'requisito_egreso_id';
    public $timestamps    = false;

    protected $fillable = [
        'id_alumno',
        'horas_sociales_completadas',
        'horas_sociales_aprobadas',
        'documento_constancia_social',
        'requiere_pasantia',
        'documento_pasantia', 
        'pasantia_aprobada',
        'institucion_pasantia',
        'solvencia_financiera',
        'estado_egreso',
        'aprobado_por',
        'fecha_egreso',
    ];

    protected function casts(): array
    {
        return [
            'horas_sociales_completadas' => 'integer',
            'horas_sociales_aprobadas'   => 'boolean',
            'requiere_pasantia'          => 'boolean',
            'pasantia_aprobada'          => 'boolean',
            'solvencia_financiera'       => 'boolean',
            'estado_egreso'              => EstadoEgreso::class,
            'fecha_egreso'               => 'date',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'id_alumno', 'alumno_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por', 'id');
    }

    public static function paraAlumno(int $alumnoId): self
    {
        return self::firstOrCreate(
            ['id_alumno' => $alumnoId],
            [
                'horas_sociales_completadas' => 0,
                'horas_sociales_aprobadas'   => false,
                'solvencia_financiera'       => false,
                'estado_egreso'              => EstadoEgreso::EN_PROCESO->value,
            ]
        );
    }
}