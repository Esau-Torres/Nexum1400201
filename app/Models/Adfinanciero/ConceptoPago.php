<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class ConceptoPago extends Model
{
    // 1. Indicar el nombre exacto de la tabla (Laravel por defecto buscaría "concepto_pagos")
    protected $table = 'concepto_pago';

    // 2. Indicar el nombre de la llave primaria
    protected $primaryKey = 'concepto_pago_id';

    // 3. Desactivar timestamps ya que tu tabla no tiene created_at ni updated_at
    public $timestamps = false;

    // 4. Campos permitidos para inserción (Mass Assignment)
    protected $fillable = [
        'codigo_concepto',
        'nombre',
        'monto_base',
        'categoria',
        'aplica_descuento_estudiante',
        'es_recurrente',
        'estado'
    ];

    // 5. Casteo de variables para asegurar el tipo de dato correcto
    protected $casts = [
        'monto_base' => 'decimal:2',
        'aplica_descuento_estudiante' => 'boolean',
        'es_recurrente' => 'boolean',
        'estado' => 'boolean',
    ];

}
