<?php

namespace App\Models\Estudiante;

use App\Models\Academico\Evaluaciones;
use App\Models\Adfinanciero\Pago;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenDiferido extends Model
{
    protected $table = 'examen_diferido';
    protected $primaryKey = 'diferido_id';
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_inscripcion',
        'id_evaluacion_original',
        'id_pago',
        'justificacion',
        'estado',
        'fecha_programada',
        'revisado_por',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
    ];

    public function detalleInscripcion(): BelongsTo
    {
        return $this->belongsTo(DetalleInscripcion::class, 'id_detalle_inscripcion', 'detalle_inscripcion_id');
    }

    public function evaluacionOriginal(): BelongsTo
    {
        return $this->belongsTo(Evaluaciones::class, 'id_evaluacion_original', 'evaluacion_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'pago_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por', 'id');
    }
}
