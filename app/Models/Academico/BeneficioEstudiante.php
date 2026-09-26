<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Users\User;
use App\Models\Estudiante\Alumnos;
use App\Models\Academico\CargoEstudiante;
use App\Enums\TipoBeneficio;

final class BeneficioEstudiante extends Model
{
    use HasFactory;

    protected $table = 'beneficios_estudiante';
    protected $primaryKey = 'beneficio_id';

    protected $fillable = [
        'id_alumno',
        'id_ciclo_lectivo',
        'tipo_beneficio',
        'nombre_convenio',
        'porcentaje_estudiante',
        'porcentaje_universidad',
        'monto_fijo_cuota',
        'resolucion_academica',
        'activo',
        'asignado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo_beneficio'         => TipoBeneficio::class,  
            'porcentaje_estudiante'  => 'decimal:2',
            'porcentaje_universidad' => 'decimal:2',
            'monto_fijo_cuota'       => 'decimal:2',
            'activo'                 => 'boolean',
            'created_at'             => 'immutable_datetime',
            'updated_at'             => 'immutable_datetime',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'id_alumno', 'alumno_id');
    }

    // este modelo no lo tengo creado aun crealo segun la tabla de la db
    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por', 'id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(CargoEstudiante::class, 'id_beneficio', 'beneficio_id');
    }
}