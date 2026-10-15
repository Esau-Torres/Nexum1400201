<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\CoordinadorFacultad\SeccionHorario;
use App\Models\CoordinadorFacultad\Aula;
use App\Models\Docente\Asistencia;


class ClaseSesion extends Model
{
    protected $table = 'clase_sesion';
    protected $primaryKey = 'clase_sesion_id';

    protected $fillable = [
        'id_seccion',
        'id_seccion_horario',
        'fecha',
        'tema',
        'id_aula',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
        ];
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'id_seccion', 'seccion_id');
    }

    public function seccionHorario(): BelongsTo
    {
        return $this->belongsTo(SeccionHorario::class, 'id_seccion_horario', 'seccion_horario_id');
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'id_aula', 'aula_id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_sesion', 'clase_sesion_id');
    }
}