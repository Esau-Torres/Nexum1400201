<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'pago_id';
    public $timestamps = false;

    protected $fillable = [
        'id_alumno',
        'numero_recibo',
        'fecha_pago',
        'canal_pago',
        'metodo_pago',
        'id_cajero',
        'id_promocion',
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha_pago' => 'datetime'
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePago::class, 'id_pago', 'pago_id');
    }
}
