<?php

namespace App\Models\Estudiante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasOne; 

class Carreras Extends Model {

    protected $table = 'carreras';
    protected $primaryKey = 'carrera_id';
    public $timestamps = false;

    // campos de la tabla
    protected $fillable = [
        'id_facultad', 'nombre', 'nivel_academico', 'duracion_ciclos'
    ];

    // relaciones regla: el modelo que tiene la llave foránea física en la base de datos (id_facultad) siempre lleva el belongsTo.
    public function facultad(): BelongsTo  {
        return $this->belongsTo(Facultades::class, 'id_facultad', 'facultad_id');
    }

     public function alumno(): HasOne {
        return $this->hasOne(Alumnos::class, 'id_carrera', 'carrera_id');
    }
}