<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluaciones extends Model
{
    protected $table = 'evaluaciones';
    protected $primaryKey = 'evaluacion_id';
    public $timestamps = false;

    protected $fillable = [
        'id_categoria',
        'nombre_evaluacion',
        'tipo_instrumento',
        'porcentaje_actividad',
        'fecha_aplicacion',
        'fecha_limite_calificacion',
        'fecha_limite_revision',
        'estado',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaEvaluacion::class, 'id_categoria', 'categoria_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'id_evaluacion', 'evaluacion_id');
    }
}
