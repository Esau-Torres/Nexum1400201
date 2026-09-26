<?php

namespace App\Models\Estudiante;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 

use Illuminate\Database\Eloquent\Model;

class Alumnos extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'alumno_id';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario', 'id_carrera', 'codigo_estudiante', 
        'correo_institucional', 'estado_carrera'
    ];

    // relaciones
    public function documentos(): HasOne {
        return $this->hasOne(AlumnoDocumentos::class, 'id_alumno', 'alumno_id');
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carreras::class, 'id_carrera', 'carrera_id');
    }

    public function beneficios(): HasMany
    {
        return $this->hasMany(BeneficioEstudiante::class, 'id_alumno', 'alumno_id');
    }

    public function beneficioCicloActual(): HasOne
    {
        return $this->hasOne(BeneficioEstudiante::class, 'id_alumno', 'alumno_id')
            ->where('activo', true)
            ->latestOfMany('beneficio_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(CargoEstudiante::class, 'id_alumno', 'alumno_id');
    }
}