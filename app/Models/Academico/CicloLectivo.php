<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Academico\BeneficioEstudiante;
use App\Models\Academico\CargoEstudiante;

final class CicloLectivo extends Model
{
    protected $table      = 'ciclo_lectivo';
    protected $primaryKey = 'ciclo_lectivo_id';
    public $timestamps    = false;

    protected $fillable = [
        'nombre_ciclo',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
        ];
    }

    public function beneficios(): HasMany
    {
        return $this->hasMany(BeneficioEstudiante::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(CargoEstudiante::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }
}