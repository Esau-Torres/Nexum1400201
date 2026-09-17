<?php

namespace App\Models\Estudiante;

use Illuminate\Database\Eloquent\Model;

class AlumnoDocumentos extends Model
{
    protected $table = 'alumno_documentos';
    protected $primaryKey = 'documento_id';
    public $timestamps = false;

    protected $fillable = [
        'id_alumno', 'titulo_bachillerato', 'partida_nacimiento', 
        'fotografia_personal', 'constancia_paes', 'estado_documentos', 'observaciones', 'revisado_por'
    ];
}