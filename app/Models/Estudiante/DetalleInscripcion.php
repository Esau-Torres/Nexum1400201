<?php

namespace App\Models\Estudiante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Estudiante\Inscripcion;
use App\Models\User;
use App\Models\Asistencia\Asistencia;
use App\Models\Academico\Calificacion;
use App\Models\Estudiante\ExamenDiferido;

class DetalleInscripcion extends Model
{
    protected $table = 'detalle_inscripcion';

    protected $primaryKey = 'detalle_inscripcion_id';

    public $timestamps = false;

    protected $fillable = [
        'id_inscripcion',
        'id_seccion',
        'numero_matricula',
        'promedio_final',
        'estado_materia',
        'es_sobrecupo',
        'autorizado_por',
        'fecha_retiro',
    ];

    protected function casts(): array
    {
        return [
            'numero_matricula' => 'integer',
            'promedio_final'   => 'decimal:2',
            'es_sobrecupo'     => 'boolean',
            'fecha_retiro'     => 'datetime',
        ];
    }

    /**
     * Inscripción a la que pertenece el detalle.
     */
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(
            Inscripcion::class,
            'id_inscripcion',
            'inscripcion_id'
        );
    }

    /**
     * Sección académica inscrita.
     */
    public function seccion(): BelongsTo
    {
        return $this->belongsTo(
            Seccion::class,
            'id_seccion',
            'seccion_id'
        );
    }

    /**
     * Usuario que autorizó el sobrecupo, si corresponde.
     */
    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'autorizado_por',
            'id'
        );
    }

    /**
     * Asistencias relacionadas con este detalle.
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(
            Asistencia::class,
            'id_detalle_inscripcion',
            'detalle_inscripcion_id'
        );
    }

    /**
     * Calificaciones relacionadas con este detalle.
     */
    public function calificaciones(): HasMany
    {
        return $this->hasMany(
            Calificacion::class,
            'id_detalle_inscripcion',
            'detalle_inscripcion_id'
        );
    }

    /**
     * Exámenes diferidos relacionados con este detalle.
     */
    public function examenesDiferidos(): HasMany
    {
        return $this->hasMany(
            ExamenDiferido::class,
            'id_detalle_inscripcion',
            'detalle_inscripcion_id'
        );
    }
}
