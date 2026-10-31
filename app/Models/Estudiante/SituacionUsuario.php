<?php

namespace App\Models\Estudiante;

use App\Enums\EstadoSituacion;
use App\Enums\TipoSituacion;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SituacionUsuario extends Model
{
    protected $table      = 'situaciones_usuario';
    protected $primaryKey = 'id';
    public $timestamps    = false;

    protected $fillable = [
        'id_usuario',
        'nombre_usuario',
        'carnet_institucional',
        'correo',
        'tipo_situacion',
        'detalle',
        'estado',
        'creado_en',
        'actualizado_por',
        'actualizado_en',
    ];

    protected function casts(): array
    {
        return [
            'tipo_situacion' => TipoSituacion::class,
            'estado'         => EstadoSituacion::class,
            'creado_en'      => 'datetime',
            'actualizado_en' => 'datetime',
            'actualizado_por'=> 'integer', 
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por', 'id');
    }

    public function scopeDeUsuario($query, int $idUsuario)
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopeDeTipo($query, TipoSituacion $tipo)
    {
        return $query->where('tipo_situacion', $tipo->value);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('creado_en');
    }

    public function scopeVisiblesParaAlumno($query)
    {
        return $query->where('estado', '!=', EstadoSituacion::CERRADO->value);
    }

    public function esEliminable(): bool
    {
        return $this->estado->puedeEliminar();
    }
}