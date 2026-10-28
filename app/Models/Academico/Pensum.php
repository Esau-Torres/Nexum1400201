<?php

namespace App\Models\Academico;

use App\Enums\EstadoPensum;
use App\Models\Estudiante\Carreras;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pensum extends Model
{
    protected $table      = 'pensum';
    protected $primaryKey = 'pensum_id';
    public $timestamps    = false;

    protected $fillable = [
        'id_carrera',
        'anio_vigencia',
        'nombre',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPensum::class,
        ];
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carreras::class, 'id_carrera', 'carrera_id');
    }

    public function pensumMaterias(): HasMany
    {
        return $this->hasMany(PensumMateria::class, 'id_pensum', 'pensum_id');
    }

    // -- Helpers --

    public function nombreCompleto(): string
    {
        return $this->nombre . " · " . ($this->carrera?->nombre ?? 'Sin carrera') . " ({$this->anio_vigencia})";
    }

    public function totalMaterias(): int
    {
        return $this->pensumMaterias->count();
    }

    public function totalUV(): int
    {
        return (int) $this->pensumMaterias->sum(fn ($pm) => $pm->materia?->unidades_valorativas ?? 0);
    }

    /**
     * Devuelve un array [ciclo => [materias...]].
     */
    public function mallaPorCiclo(): array
    {
        return $this->pensumMaterias
            ->sortBy('ciclo_sugerido')
            ->groupBy('ciclo_sugerido')
            ->toArray();
    }

    /**
     * Ciclos usados (los que tienen al menos una materia).
     */
    public function ciclosUsados(): array
    {
        return $this->pensumMaterias->pluck('ciclo_sugerido')->unique()->sort()->values()->all();
    }
}