<?php

namespace App\Models\Academico;

use App\Enums\EstadoMateria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Docente\Seccion;
use App\Models\Academico\pensumMateria;

class Materias extends Model
{
    protected $table      = 'materias';
    protected $primaryKey = 'materia_id';
    public $timestamps    = false;

    protected $fillable = [
        'codigo_materia',
        'nombre',
        'unidades_valorativas',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoMateria::class,
        ];
    }

    // -- Relaciones --
    public function pensumMaterias(): HasMany
    {
        return $this->hasMany(PensumMateria::class, 'id_materia', 'materia_id');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'id_materia', 'materia_id');
    }
}