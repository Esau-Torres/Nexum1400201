<?php

namespace App\Models\Academico;

use App\Enums\EstadoProcesoGraduacion;
use App\Enums\ModalidadGraduacion;
use App\Models\Adfinanciero\Pago;
use App\Models\Estudiante\Alumnos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoGraduacion extends Model
{
    protected $table      = 'proceso_graduacion';
    protected $primaryKey = 'proceso_id';
    public $timestamps    = true;

    protected $fillable = [
        'id_alumno',
        'modalidad',
        'id_grupo',
        'id_pago_derechos_grado',
        'nombre_curso',
        'intentos_defensa',
        'nota_defensa',
        'estado_proceso',
        'fecha_defensa',
        'fecha_graduacion',
        'es_activo',
    ];

    protected function casts(): array
    {
        return [
            'modalidad'        => ModalidadGraduacion::class,
            'estado_proceso'   => EstadoProcesoGraduacion::class,
            'nota_defensa'     => 'decimal:2',
            'intentos_defensa' => 'integer',
            'fecha_defensa'    => 'date',
            'fecha_graduacion' => 'date',
            'es_activo' => 'boolean',
        ];
    }
    
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'id_alumno', 'alumno_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoGraduacion::class, 'id_grupo', 'grupo_id');
    }

    public function pagoDerechosGrado(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago_derechos_grado', 'pago_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('es_activo', true);
    }
}