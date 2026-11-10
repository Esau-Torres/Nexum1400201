<?php

namespace App\Models\CoordinadorFacultad;

use App\Enums\TipoAula;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CoordinadorFacultad\Edificio;
use App\Models\CoordinadorFacultad\SeccionHorario;
use App\Models\CoordinadorFacultad\ClaseSesion;

class Aula extends Model
{
    protected $table = 'aula';
    protected $primaryKey = 'aula_id';

    protected $fillable = [
        'id_edificio',
        'nombre_aula',
        'nivel',
        'capacidad',
        'tipo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'nivel'     => 'integer',
            'capacidad' => 'integer',
            'tipo'      => TipoAula::class,
            'activo'    => 'boolean',
        ];
    }

    public function edificio(): BelongsTo
    {
        return $this->belongsTo(Edificio::class, 'id_edificio', 'edificio_id');
    }

    public function seccionesHorario(): HasMany
    {
        return $this->hasMany(SeccionHorario::class, 'id_aula', 'aula_id');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(ClaseSesion::class, 'id_aula', 'aula_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /** Nombre completo tipo "Edificio A · Nivel 2 · Aula 205" */
    public function getNombreCompletoAttribute(): string
    {
        $edificio = $this->edificio?->nombre ?? 'Sin edificio';
        return "{$edificio} · Nivel {$this->nivel} · {$this->nombre_aula}";
    }
}