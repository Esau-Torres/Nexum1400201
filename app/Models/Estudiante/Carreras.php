<?php

namespace App\Models\Estudiante;

use App\Enums\EstadoPensum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Academico\Pensum;
use App\Models\Estudiante\Facultades;
use App\Models\Academico\NivelAcademico;
use App\Models\Estudiante\Alumnos;
use Illuminate\Support\Facades\Cache;

class Carreras Extends Model {

    protected $table = 'carreras';
    protected $primaryKey = 'carrera_id';
    public $timestamps = false;

    // campos de la tabla
    protected $fillable = [
        'id_facultad', 'nombre', 'id_nivel_academico', 'duracion_ciclos'
    ];

    protected function casts(): array
    {
        return [
            'duracion_ciclos' => 'integer',
        ];
    }

    /** Clave de cache — centralizada para evitar typos al invalidar */
    public const CACHE_KEY_ACTIVAS_CON_PENSUM = 'carreras_con_pensum_activo';

    // -- Relaciones --
    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultades::class, 'id_facultad', 'facultad_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelAcademico::class, 'id_nivel_academico', 'nivel_academico_id');
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(Alumnos::class, 'id_carrera', 'carrera_id');
    }

    public function pensums(): HasMany
    {
        return $this->hasMany(Pensum::class, 'id_carrera', 'carrera_id');
    }

    // -- SCOPE --
    public function scopeConPensumActivo(Builder $query): Builder
    {
        return $query->whereHas('pensums', function (Builder $q) {
            $q->where('estado', EstadoPensum::ACTIVO->value);
        });
    }

    public static function activasConPensum()
    {
        $ids = Cache::remember(
            self::CACHE_KEY_ACTIVAS_CON_PENSUM,
            now()->addMinutes(30),
            fn () => static::query()
                ->conPensumActivo()
                ->orderBy('nombre')
                ->pluck('carrera_id')
                ->toArray()
        );

        if (empty($ids)) {
            return static::query()->whereRaw('1 = 0')->get(); // colección vacía
        }

        // Reordenamos según el orden que traía el cache
        return static::query()
            ->whereIn('carrera_id', $ids)
            ->orderByRaw('array_position(ARRAY[' . implode(',', array_map('intval', $ids)) . ']::int[], carrera_id)')
            ->get();
    }

    public static function olvidarCacheActivas(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVAS_CON_PENSUM);
    }

    // -- Helpers --

    public function tienePensum(): bool
    {
        return $this->pensums()->exists();
    }

    public function tienePensumActivo(): bool
    {
        return $this->pensums()->where('estado', 'ACTIVO')->exists();
    }
}
