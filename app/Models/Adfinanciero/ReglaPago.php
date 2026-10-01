<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class ReglaPago extends Model
{
    // 1. Apuntar a la tabla correcta
    protected $table = 'reglas_pago';

    // 2. Definir la llave primaria personalizada
    protected $primaryKey = 'regla_id';

    // 3. Desactivar timestamps si no creaste created_at y updated_at
    public $timestamps = false;

    // 4. Campos permitidos para inserción masiva
    protected $fillable = [
        'tipo',
        'dia_inicio_ordinario',
        'dia_fin_ordinario',
        'dia_inicio_extra',
        'dia_fin_extra',
        'id_concepto_recargo',
        'estado'
    ];

    // 5. Relación con el concepto de recargo (Mora, Inscripción extraordinaria, etc.)
    public function conceptoRecargo()
    {
        // Cambia 'ConceptoPago::class' por el nombre y ruta real de tu modelo de conceptos si es diferente
        return $this->belongsTo(\App\Models\Adfinanciero\ConceptoPago::class, 'id_concepto_recargo', 'concepto_pago_id');
    }
}
