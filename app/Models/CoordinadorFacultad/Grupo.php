<?php

namespace App\Models\CoordinadorFacultad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Academico\CicloLectivo;
use App\Models\CoordinadorFacultad\Horarios;
use App\Models\CoordinadorFacultad\GrupoHorario;
use App\Models\CoordinadorFacultad\Seccion;

class Grupo extends Model
{
    protected $table = 'grupo';
    protected $primaryKey = 'grupo_id';

    protected $fillable = [
        'id_ciclo_lectivo',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function horarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Horarios::class,
            'grupo_horario',
            'id_grupo',
            'id_horario',
            'grupo_id',
            'horario_id'
        )->withPivot('grupo_horario_id');
    }

    public function grupoHorarios(): HasMany
    {
        return $this->hasMany(GrupoHorario::class, 'id_grupo', 'grupo_id');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'id_grupo', 'grupo_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /** Total de alumnos activos del grupo (según detalle_inscripcion) */
    public function getAlumnosCountAttribute(): int
    {
        return \DB::table('detalle_inscripcion')
            ->join('seccion', 'seccion.seccion_id', '=', 'detalle_inscripcion.id_seccion')
            ->where('seccion.id_grupo', $this->grupo_id)
            ->whereIn('detalle_inscripcion.estado_materia', ['CURSANDO', 'APROBADA'])
            ->distinct('detalle_inscripcion.id_inscripcion')
            ->count('detalle_inscripcion.id_inscripcion');
    }
}