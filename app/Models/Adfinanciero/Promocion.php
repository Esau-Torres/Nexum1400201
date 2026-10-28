<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'promociones';
    protected $primaryKey = 'promocion_id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'porcentaje_descuento',
        'id_concepto_aplicable',
        'fecha_inicio',
        'fecha_fin',
        'requiere_solvencia_hasta',
        'estado'
    ];

    protected $casts = [
        'porcentaje_descuento' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'requiere_solvencia_hasta' => 'date',
        'estado' => 'boolean'
    ];

    // Relación para obtener los datos del concepto al que aplica la promoción
    public function concepto()
    {
        // Usamos la ruta absoluta con la barra invertida inicial (\App\...)
        return $this->belongsTo(\App\Models\Adfinanciero\ConceptoPago::class, 'id_concepto_aplicable', 'concepto_pago_id');
    }


}
