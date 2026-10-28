<?php

namespace App\Models\CoordinadorFacultad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Docente\Docentes;
use App\Models\Academico\CicloLectivo;
use App\Models\CoordinadorFacultad\Aula;
use App\Models\CoordinadorFacultad\Horarios;
use App\Models\CoordinadorFacultad\SeccionHorario;

class ClaseCompartida extends Model
{
    protected $table = 'clase_compartida';
    protected $primaryKey = 'clase_compartida_id';
    public $timestamps = false;

    protected $fillable = [
        'id_docente',
        'id_ciclo_lectivo',
        'id_aula',
        'id_horario',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docentes::class, 'id_docente', 'docente_id');
    }

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'id_aula', 'aula_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horarios::class, 'id_horario', 'horario_id');
    }

    public function seccionesHorario(): HasMany
    {
        return $this->hasMany(SeccionHorario::class, 'id_clase_compartida', 'clase_compartida_id');
    }

    /** Total de alumnos (cupos) de todas las secciones que usan esta clase */
    public function getCupoTotalAttribute(): int
    {
        return $this->seccionesHorario()
            ->join('seccion', 'seccion.seccion_id', '=', 'seccion_horario.id_seccion')
            ->sum('seccion.cupo_maximo');
    }
}