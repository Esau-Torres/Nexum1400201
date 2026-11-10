<?php

namespace App\Models\Academico;

use App\Enums\TamanoGrupo;
use App\Models\Docente\Docentes;
use App\Models\Academico\ProcesoGraduacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoGraduacion extends Model
{
    protected $table      = 'grupos_graduacion';
    protected $primaryKey = 'grupo_id';
    public $timestamps    = false;

    protected $fillable = [
        'codigo_grupo',
        'titulo_tema',
        'id_docente_asesor',
        'estado_grupo',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function docenteAsesor(): BelongsTo
    {
        return $this->belongsTo(Docentes::class, 'id_docente_asesor', 'docente_id');
    }

    public function procesos(): HasMany
    {
        return $this->hasMany(ProcesoGraduacion::class, 'id_grupo', 'grupo_id');
    }

    /**
     * Tamaño del grupo (1, 2 o 3) según cuántos procesos activos tiene.
     */
    public function tamano(): TamanoGrupo
    {
        return TamanoGrupo::desdeIntegrantes($this->procesos()->count());
    }
}