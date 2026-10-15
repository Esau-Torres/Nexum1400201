<?php

namespace App\Models\CoordinadorFacultad;

use App\Enums\EstadoSeccion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Docente\Docentes;
use App\Models\Academico\CicloLectivo;
use App\Models\Academico\Materias;
use App\Models\CoordinadorFacultad\SeccionHorario;
use App\Models\CoordinadorFacultad\ClaseSesion;
use App\Models\Estudiante\DetalleInscripcion;

class Seccion extends Model
{
    protected $table = 'seccion';
    protected $primaryKey = 'seccion_id';

    protected $fillable = [
        'id_materia',
        'id_ciclo_lectivo',
        'id_docente',
        'nombre_grupo',
        'cupo_maximo',
        'cupo_extra_maximo',
        'permite_sobrecupo',
        'estado',
        'id_grupo'
    ];

    protected function casts(): array
    {
        return [
            'cupo_maximo'       => 'integer',
            'cupo_extra_maximo' => 'integer',
            'permite_sobrecupo' => 'boolean',
            'estado'            => EstadoSeccion::class,
        ];
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materias::class, 'id_materia', 'materia_id');
    }

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docentes::class, 'id_docente', 'docente_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(SeccionHorario::class, 'id_seccion', 'seccion_id');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(ClaseSesion::class, 'id_seccion', 'seccion_id');
    }

    public function detallesInscripcion(): HasMany
    {
        return $this->hasMany(DetalleInscripcion::class, 'id_seccion', 'seccion_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', EstadoSeccion::ACTIVA->value);
    }

    /** Cupo total disponible (base + extra si permite sobrecupo) */
    public function getCupoTotalAttribute(): int
    {
        return $this->cupo_maximo + ($this->permite_sobrecupo ? $this->cupo_extra_maximo : 0);
    }

    /** Inscritos activos actuales */
    public function getInscritosCountAttribute(): int
    {
        return $this->detallesInscripcion()
            ->whereIn('estado_materia', ['CURSANDO', 'APROBADA'])
            ->count();
    }

    /** Cupo disponible restante */
    public function getCupoDisponibleAttribute(): int
    {
        return max(0, $this->cupo_total - $this->inscritos_count);
    }
}