<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Estudiante\Carreras;

class NivelAcademico extends Model
{
    protected $table      = 'niveles_academicos';
    protected $primaryKey = 'nivel_academico_id';
    public $timestamps    = false;

    protected $fillable = ['codigo', 'nombre', 'activo', 'orden'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden'  => 'integer',
        ];
    }

    public function carreras(): HasMany
    {
        return $this->hasMany(Carreras::class, 'id_nivel_academico', 'nivel_academico_id');
    }
}