<?php

namespace App\Models\Estudiante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 

class AlumnoDocumentos extends Model
{
    protected $table = 'alumno_documentos';
    protected $primaryKey = 'documento_id';
    public $timestamps = false;

    protected $fillable = [
        'id_alumno', 'titulo_bachillerato', 'partida_nacimiento', 
        'fotografia_personal', 'constancia_paes', 'estado_documentos', 'observaciones', 'revisado_por'
    ];

    public const CAMPOS_OBLIGATORIOS = [
        'titulo_bachillerato',
        'partida_nacimiento',
        'fotografia_personal',
        'constancia_paes',
    ];
    // relaciones
    public function alumno(): BelongsTo {
        return $this->belongsTo(Alumnos::class, 'id_alumno', 'alumno_id');
    }
    
    // helpers para validacion de estados de documentos.
    public function estaCompleto(): bool
    {
        foreach (self::CAMPOS_OBLIGATORIOS as $campo) {
            if (empty($this->$campo)) {
                return false;
            }
        }
        return true;
    }
    
    public function recalcularEstado(bool $forzar = false): string
    {
        if (!$forzar && !in_array($this->estado_documentos, ['PENDIENTE', 'APROBADO'], true)) {
            return $this->estado_documentos;
        }

        $nuevo = $this->estaCompleto() ? 'APROBADO' : 'PENDIENTE';

        if ($this->estado_documentos !== $nuevo) {
            $this->estado_documentos    = $nuevo;
            $this->fecha_actualizacion  = now();
            $this->save();
        }

        return $nuevo;
    }
}