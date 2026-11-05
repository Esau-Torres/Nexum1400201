<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEvaluacion extends Model
{
    protected $table = 'periodo_evaluacion';
    protected $primaryKey = 'periodo_evaluacion_id';
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo_lectivo',
        'nombre',
        'numero_periodo',
        'porcentaje_periodo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaEvaluacion::class, 'id_periodo_evaluacion', 'periodo_evaluacion_id');
    }
}
