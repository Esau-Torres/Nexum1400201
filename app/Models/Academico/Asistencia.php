<?php

namespace App\Models\Academico;

use App\Models\Estudiante\DetalleInscripcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $table = 'asistencia';
    protected $primaryKey = 'asistencia_id';
    public $timestamps = false; // La tabla no tiene timestamps según el dump

    protected $fillable = [
        'id_sesion',
        'id_detalle_inscripcion',
        'estado',
        'observacion',
    ];

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(ClaseSesion::class, 'id_sesion', 'clase_sesion_id');
    }

    public function detalleInscripcion(): BelongsTo
    {
        return $this->belongsTo(DetalleInscripcion::class, 'id_detalle_inscripcion', 'detalle_inscripcion_id');
    }
}
