<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\EstadoCiclo;
use App\Enums\TipoCiclo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute; 

use App\Models\Academico\BeneficioEstudiante;
use App\Models\Academico\CargoEstudiante;

final class CicloLectivo extends Model
{
    protected $table      = 'ciclo_lectivo';
    protected $primaryKey = 'ciclo_lectivo_id';
    public $timestamps    = false;

    protected $fillable = [
        'nombre_ciclo',
        'fecha_inicio',
        'fecha_fin',
        'anio',
        'numero_ciclo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
            'estado'       => EstadoCiclo::class,
            'numero_ciclo' => TipoCiclo::class,
        ];
    }

    public function beneficios(): HasMany
    {
        return $this->hasMany(BeneficioEstudiante::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(CargoEstudiante::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    // --- LÓGICA DE NEGOCIO ---

    /**
     * Determina si este ciclo puede ser creado.
     * Regla: No se puede crear un nuevo ciclo si otro está en curso.
     */
    public static function puedeCrearNuevoCiclo(): bool
    {
        return !self::whereIn('estado', [
            EstadoCiclo::CONFIGURACION,
            EstadoCiclo::MATRICULA_ABIERTA,
            EstadoCiclo::ACTIVO,
        ])->exists();
    }

    /**
     * Verifica si el ciclo puede cambiar a un estado específico.
     */
    public function puedeCambiarEstadoA(EstadoCiclo $nuevoEstado): bool
    {
        // Si ya está cerrado, es el estado final. No se puede cambiar.
        if ($this->estado === EstadoCiclo::CERRADO) {
            return false;
        }

        // Si se intenta pasar a CERRADO, la fecha de fin debe haber pasado.
        if ($nuevoEstado === EstadoCiclo::CERRADO) {
            return $this->fecha_fin->isPast();
        }

        // Aquí puedes añadir más reglas de transición de estado si quieres.
        // Por ejemplo, no puedes pasar de 'CONFIGURACION' directamente a 'ACTIVO'.
        // return match ($this->estado) { ... };

        return true; // Por defecto, permite el cambio si no es a CERRADO.
    }

    /**
     * Cierra el ciclo lectivo.
     */
    public function cerrar(): void
    {
        // Doble chequeo de seguridad a nivel de modelo.
        if (!$this->puedeCambiarEstadoA(EstadoCiclo::CERRADO)) {
            throw new \Exception('No se puede cerrar un ciclo cuya fecha de finalización no ha pasado.');
        }
        $this->estado = EstadoCiclo::CERRADO;
        $this->save();

        // Aquí podrías disparar eventos como CierreDeCicloEvent::dispatch($this);
    }

    // Accessor para el nombre completo, si no lo tienes ya.
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => "CICLO {$this->numero_ciclo->value}-{$this->anio}"
        );
    }
}