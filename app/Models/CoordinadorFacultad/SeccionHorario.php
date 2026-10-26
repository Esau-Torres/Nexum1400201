<?php

namespace App\Models\CoordinadorFacultad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\CoordinadorFacultad\Horarios;
use App\Models\CoordinadorFacultad\Aula;
use App\Models\CoordinadorFacultad\ClaseSesion;
use App\Models\CoordinadorFacultad\ClaseCompartida;

class SeccionHorario extends Model
{
    protected $table = 'seccion_horario';
    protected $primaryKey = 'seccion_horario_id';
    public $timestamps = false;

    protected $fillable = [
        'id_seccion',
        'id_horario',
        'id_aula',
        'id_clase_compartida',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'id_seccion', 'seccion_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horarios::class, 'id_horario', 'horario_id');
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'id_aula', 'aula_id');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(ClaseSesion::class, 'id_seccion_horario', 'seccion_horario_id');
    }

    public function claseCompartida(): BelongsTo
    {
        return $this->belongsTo(ClaseCompartida::class, 'id_clase_compartida', 'clase_compartida_id');
    }
}