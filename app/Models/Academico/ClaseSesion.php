<?php

namespace App\Models\Academico;

use App\Models\CoordinadorFacultad\Seccion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaseSesion extends Model
{
    protected $table = 'clase_sesion';
    protected $primaryKey = 'clase_sesion_id';
    public $timestamps = true; // La tabla tiene created_at y updated_at

    protected $fillable = [
        'id_seccion',
        'fecha',
        'tema',
        'id_seccion_horario',
        'id_aula',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'id_seccion', 'seccion_id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_sesion', 'clase_sesion_id');
    }
}
