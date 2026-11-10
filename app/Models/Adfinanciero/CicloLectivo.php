<?php

namespace App\Models\Adfinanciero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CicloLectivo extends Model
{
    protected $table = 'ciclo_lectivo';
    protected $primaryKey = 'ciclo_lectivo_id';
    public $timestamps = false;

    protected $fillable = ['nombre_ciclo', 'fecha_inicio', 'fecha_fin', "Estado"];

    protected static function booted()
    {
        static::saved(function ($ciclo) {

            // ESCENARIO 1: Se abrió un nuevo ciclo (Se puso en ACTIVO)
            if ($ciclo->estado === 'ACTIVO') {
                // Encendemos automáticamente todas las reglas de pago
                DB::table('reglas_pago')->update(['estado' => 'ACTIVO']);
            }

            // ESCENARIO 2: Se terminó el semestre (Se puso en CERRADO)
            if ($ciclo->estado === 'CERRADO') {
                // Revisamos si no quedó NINGÚN otro ciclo activo por error
                $hayActivos = DB::table('ciclo_lectivo')->where('estado', 'ACTIVO')->exists();

                if (!$hayActivos) {
                    // Como la universidad está inactiva, apagamos todas las reglas
                    DB::table('reglas_pago')->update(['estado' => 'INACTIVO']);
                }
            }

        });
    }

}
