<?php

namespace App\Models\Academico;

use App\Models\Estudiante\DetalleInscripcion;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    protected $table = 'calificacion';
    protected $primaryKey = 'calificacion_id';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion',
        'id_detalle_inscripcion',
        'nota',
        'observacion',
        'es_nota_diferida',
        'modificado_por',
        'fecha_registro',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
        'es_nota_diferida' => 'boolean',
    ];

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluaciones::class, 'id_evaluacion', 'evaluacion_id');
    }

    public function detalleInscripcion(): BelongsTo
    {
        return $this->belongsTo(DetalleInscripcion::class, 'id_detalle_inscripcion', 'detalle_inscripcion_id');
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por', 'id');
    }
}
