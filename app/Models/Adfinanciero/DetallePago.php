<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class DetallePago extends Model
{
    protected $table = 'detalle_pago';
    protected $primaryKey = 'detalle_pago_id';
    public $timestamps = false;

    protected $fillable = [
        'id_pago',
        'id_cargo',
        'id_concepto',
        'monto_base',
        'descuento_aplicado',
        'recargo_aplicado',
        'cantidad',
        'subtotal'
    ];

    protected $casts = [
        'monto_base' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'recargo_aplicado' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function concepto()
    {
        return $this->belongsTo(ConceptoPago::class, 'id_concepto', 'concepto_pago_id');
    }
}
