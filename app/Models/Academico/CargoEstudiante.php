<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Estudiante\Alumnos;
use App\Models\Academico\BeneficioEstudiante;

final class CargoEstudiante extends Model
{
    use HasFactory;

    protected $table = 'cargo_estudiante';
    protected $primaryKey = 'cargo_id';
    public $timestamps = false;

    protected $fillable = [
        'id_alumno',
        'id_concepto',
        'id_ciclo_lectivo',
        'id_beneficio',
        'mes_arancel',
        'anio_arancel',
        'monto_original',
        'monto_absorbido_universidad',
        'monto_neto_estudiante',
        'fecha_emision',
        'fecha_vencimiento',
        'estado_cargo',
    ];

    protected function casts(): array
    {
        return [
            'mes_arancel' => 'integer',
            'anio_arancel' => 'integer',
            'monto_original' => 'decimal:2',
            'monto_absorbido_universidad' => 'decimal:2',
            'monto_neto_estudiante' => 'decimal:2',
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'id_alumno', 'alumno_id');
    }

    // este modelo no lo tengo aun pero no lo crees esto pertenece al modulo de financiera ese modulo no me corresponde
    public function conceptoPago(): BelongsTo
    {
        return $this->belongsTo(ConceptoPago::class, 'id_concepto', 'concepto_pago_id');
    }

    public function beneficio(): BelongsTo
    {
        return $this->belongsTo(BeneficioEstudiante::class, 'id_beneficio', 'beneficio_id');
    }

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    // este modelo no lo tengo aun pero no lo crees esto pertenece al modulo de financiera ese modulo no me corresponde
    public function detallesPago(): HasMany
    {
        return $this->hasMany(DetallePago::class, 'id_cargo', 'cargo_id');
    }
}