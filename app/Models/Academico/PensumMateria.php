<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Academico\Materias;
use App\Models\Academico\pensum;
use App\Models\Academico\Prerequisito;

class PensumMateria extends Model
{
    protected $table      = 'pensum_materia';
    protected $primaryKey = 'pensum_materia_id';
    public $timestamps    = false;

    protected $fillable = [
        'id_pensum',
        'id_materia',
        'ciclo_sugerido',
        'es_obligatoria',
    ];

    protected function casts(): array
    {
        return [
            'es_obligatoria' => 'boolean',
            'ciclo_sugerido' => 'integer',
        ];
    }

    public function pensum(): BelongsTo
    {
        return $this->belongsTo(Pensum::class, 'id_pensum', 'pensum_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materias::class, 'id_materia', 'materia_id');
    }
    
    public function prerequisitos(): HasMany
    {
        return $this->hasMany(Prerequisito::class, 'id_pensum_materia', 'pensum_materia_id');
    }
}