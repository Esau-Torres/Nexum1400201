<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Users\User;
use App\Models\Academico\CicloLectivo;

class EvaluacionDocente extends Model
{
    protected $table = 'evaluacion_docente';
    protected $primaryKey = 'evaluacion_docente_id';
    public $timestamps = false;

    protected $fillable = [
        'id_seccion',
        'id_ciclo_lectivo',
        'url',
        'fecha_registro',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
        ];
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'id_seccion', 'seccion_id');
    }

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }
}