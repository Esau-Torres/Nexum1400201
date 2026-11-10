<?php

namespace App\Models\CoordinadorFacultad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CoordinadorFacultad\Grupo;
use App\Models\CoordinadorFacultad\Horarios;

class GrupoHorario extends Model
{
    protected $table = 'grupo_horario';
    protected $primaryKey = 'grupo_horario_id';
    public $timestamps = false;

    protected $fillable = [
        'id_grupo',
        'id_horario',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'grupo_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horarios::class, 'id_horario', 'horario_id');
    }
}