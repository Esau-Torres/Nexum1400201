<?php

namespace App\Models\Docente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Academico\Materias;
use App\Models\Academico\CicloLectivo;
use App\Models\Docente\Docentes;

class Seccion extends Model
{
    protected $table      = 'seccion';
    protected $primaryKey = 'seccion_id';
    public $timestamps    = false;

    protected $fillable = [
        'id_materia',
        'id_ciclo_lectivo',
        'id_docente',
        'id_aula',
        'id_horario',
        'nombre_grupo',
        'cupo_maximo',
        'cupo_extra_maximo',
        'permite_sobrecupo',
    ];

    protected function casts(): array
    {
        return [
            'permite_sobrecupo' => 'boolean',
            'cupo_maximo'       => 'integer',
            'cupo_extra_maximo' => 'integer',
        ];
    }

    // -- Relaciones --

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
    /*
    // cuando exista se descomentan
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'id_aula', 'aula_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'horario_id');
    }

    public function categoriasEvaluacion(): HasMany
    {
        return $this->hasMany(CategoriaEvaluacion::class, 'id_seccion', 'seccion_id');
    }

    public function clasesSesion(): HasMany
    {
        return $this->hasMany(ClaseSesion::class, 'id_seccion', 'seccion_id');
    }

    public function detallesInscripcion(): HasMany
    {
        return $this->hasMany(DetalleInscripcion::class, 'id_seccion', 'seccion_id');
    }
    */
    // -- Helpers --

    /**
     * Cupo total permitido (base + extra si permite sobrecupo).
     */
    public function getCupoTotalAttribute(): int
    {
        return $this->permite_sobrecupo
            ? $this->cupo_maximo + $this->cupo_extra_maximo
            : $this->cupo_maximo;
    }

    /**
     * Verifica si la sección está en un ciclo en curso.
     */
    public function estaEnCicloActivo(): bool
    {
        return $this->cicloLectivo
            && in_array($this->cicloLectivo->estado->value, ['MATRICULA_ABIERTA', 'ACTIVO'], true);
    }
}