<?php

namespace App\Models\Estudiante;

use App\Models\Academico\CicloLectivo;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';
    protected $primaryKey = 'inscripcion_id';
    public $timestamps = false;

    protected $fillable = [
        'id_alumno',
        'id_ciclo_lectivo',
        'fecha_inscripcion',
        'estado_inscripcion',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
    ];

    /**
     * Alumno al que pertenece esta inscripción.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'id_alumno', 'alumno_id');
    }

    /**
     * Ciclo lectivo en el que se realizó la inscripción.
     */
    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    /**
     * Detalles de inscripción (materias inscritas en este ciclo).
     */
    public function detallesInscripcion(): HasMany
    {
        return $this->hasMany(DetalleInscripcion::class, 'id_inscripcion', 'inscripcion_id');
    }

    /**
     * Usuario que autorizó la inscripción (si aplica).
     */
    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por', 'id');
    }

    /**
     * Scope para obtener solo inscripciones activas.
     */
    public function scopeActivas($query)
    {
        return $query->where('estado_inscripcion', 'ACTIVA');
    }
}
