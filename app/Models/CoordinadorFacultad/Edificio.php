<?php

namespace App\Models\CoordinadorFacultad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CoordinadorFacultad\Aula;

class Edificio extends Model
{
    protected $table = 'edificio';
    protected $primaryKey = 'edificio_id';

    protected $fillable = [
        'nombre',
        'niveles',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'niveles' => 'integer',
            'activo'  => 'boolean',
        ];
    }

    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'id_edificio', 'edificio_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}