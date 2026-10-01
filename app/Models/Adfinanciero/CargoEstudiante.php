<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class CargoEstudiante extends Model
{
    // Asumiendo que tu tabla se llama cargo_estudiante o ajusta este nombre si es diferente
    protected $table = 'cargo_estudiante';
    protected $primaryKey = 'cargo_id';
    public $timestamps = false;

    protected $fillable = [
        'id_alumno',
        'id_concepto',
        'id_ciclo_lectivo',
        'mes_arancel',
        'anio_arancel',
        'monto_original',
        'fecha_emision',
        'fecha_vencimiento',
        'estado_cargo'
    ];

    protected $casts = [
        'monto_original' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date'
    ];

    public function ciclo()
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function concepto()
    {
        return $this->belongsTo(ConceptoPago::class, 'id_concepto', 'concepto_pago_id');
    }
}
