<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Academico\PensumMateria;
use App\Models\Academico\Materias;

class Prerequisito extends Model
{
    protected $table      = 'prerequisitos';
    protected $primaryKey = 'prerequisito_id';
    public $timestamps    = false;

    protected $fillable = [
        'id_pensum_materia',
        'id_materia_requisito',
        'uv_minimas_requeridas',
    ];

    protected function casts(): array
    {
        return [
            'uv_minimas_requeridas' => 'integer',
        ];
    }

    public function pensumMateria(): BelongsTo
    {
        return $this->belongsTo(PensumMateria::class, 'id_pensum_materia', 'pensum_materia_id');
    }

    public function materiaRequisito(): BelongsTo
    {
        return $this->belongsTo(Materias::class, 'id_materia_requisito', 'materia_id');
    }
}