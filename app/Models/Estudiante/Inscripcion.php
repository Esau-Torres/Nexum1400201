<?php

namespace App\Models\Estudiante;

use App\Enums\EstadoInscripcion;
use App\Models\Academico\CicloLectivo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscripcion extends Model
{
    protected $table      = 'inscripciones';
    protected $primaryKey = 'inscripcion_id';
    public $timestamps    = false;

    protected $fillable = [
        'id_alumno',
        'id_ciclo_lectivo',
        'fecha_inscripcion',
        'estado_inscripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion'  => 'date',
            'estado_inscripcion' => EstadoInscripcion::class,
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'id_alumno', 'alumno_id');
    }

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleInscripcion::class, 'id_inscripcion', 'inscripcion_id');
    }
}