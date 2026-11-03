<?php

namespace App\Models\Academico;

use App\Models\CoordinadorFacultad\Seccion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaEvaluacion extends Model
{
    protected $table = 'categoria_evaluacion';
    protected $primaryKey = 'categoria_id';
    public $timestamps = false;

    protected $fillable = [
        'id_periodo_evaluacion',
        'id_seccion',
        'tipo_categoria',
        'porcentaje_categoria',
    ];

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'id_periodo_evaluacion', 'periodo_evaluacion_id');
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'id_seccion', 'seccion_id');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluaciones::class, 'id_categoria', 'categoria_id');
    }
}
