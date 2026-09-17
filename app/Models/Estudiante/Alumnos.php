<?php

namespace App\Models\Estudiante;

use Illuminate\Database\Eloquent\Model;

class Alumnos extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'alumno_id';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario', 'id_carrera', 'codigo_estudiante', 
        'correo_institucional', 'tipo_arancel', 'descuento_arancel', 'estado_carrera'
    ];

    public function documentos() {
        return $this->hasOne(AlumnoDocumentos::class, 'id_alumno');
    }
}