<?php

namespace App\Models\Academico;

use App\Enums\TipoBeneficio;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReglaBeneficio extends Model
{
    protected $table      = 'reglas_beneficio';
    protected $primaryKey = 'regla_beneficio_id';

    protected $fillable = [
        'tipo_beneficio',
        'cum_minimo',
        'nota_minima_materia',
        'max_reprobaciones_permitidas',
        'max_materias_reprobadas_ciclo',
        'uv_minimas_cursadas',
        'requiere_solvencia_financiera',
        'requiere_actividades',
        'observaciones',
        'activo',
        'vigente_desde',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tipo_beneficio'              => TipoBeneficio::class,
            'cum_minimo'                  => 'decimal:2',
            'nota_minima_materia'         => 'decimal:2',
            'max_reprobaciones_permitidas'   => 'integer',
            'max_materias_reprobadas_ciclo'  => 'integer',
            'uv_minimas_cursadas'         => 'integer',
            'requiere_solvencia_financiera'  => 'boolean',
            'requiere_actividades'        => 'boolean',
            'activo'                      => 'boolean',
            'vigente_desde'               => 'date:Y-m-d',
            'created_at'                  => 'immutable_datetime',
            'updated_at'                  => 'immutable_datetime',
        ];
    }

    // Relaciones
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /* ============================================================
       Scopes
       ============================================================ */

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where('activo', true)
                     ->where('vigente_desde', '<=', now());
    }

    public function scopeDelTipo(Builder $query, TipoBeneficio|string $tipo): Builder
    {
        $valor = $tipo instanceof TipoBeneficio ? $tipo->value : $tipo;

        return $query->where('tipo_beneficio', $valor);
    }

    /* ============================================================
       Helpers
       ============================================================ */

    /**
     * Devuelve la regla vigente para un tipo de beneficio.
     */
    public static function vigentePara(TipoBeneficio|string $tipo): ?self
    {
        $valor = $tipo instanceof TipoBeneficio ? $tipo->value : $tipo;

        return static::query()
            ->where('tipo_beneficio', $valor)
            ->where('activo', true)
            ->where('vigente_desde', '<=', now())
            ->orderByDesc('vigente_desde')
            ->first();
    }

    /**
     * ¿La regla exige antigüedad académica?
     * Si `uv_minimas_cursadas = 0`, no exige antigüedad.
     */
    public function exigeAntiguedad(): bool
    {
        return $this->uv_minimas_cursadas > 0;
    }
}