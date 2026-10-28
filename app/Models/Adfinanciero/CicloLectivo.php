<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;

class CicloLectivo extends Model
{
    protected $table = 'ciclo_lectivo';
    protected $primaryKey = 'ciclo_lectivo_id';
    public $timestamps = false;

    protected $fillable = ['nombre_ciclo', 'fecha_inicio', 'fecha_fin'];
}
