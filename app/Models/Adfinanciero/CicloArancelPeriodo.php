<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class CicloArancelPeriodo extends Model
{
    protected $table = 'ciclo_arancel_periodo';
    protected $primaryKey = 'periodo_id';
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo_lectivo',
        'fecha_inicio_ordinario',
        'fecha_fin_ordinario',
        'fecha_inicio_extraordinario',
        'fecha_fin_extraordinario',
        'id_concepto_recargo_extra'
    ];

    // Relación para traer el nombre del ciclo
    public function ciclo()
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    // Relación para traer la información del recargo
    public function recargo()
    {
        return $this->belongsTo(ConceptoPago::class, 'id_concepto_recargo_extra', 'concepto_pago_id');
    }
}
