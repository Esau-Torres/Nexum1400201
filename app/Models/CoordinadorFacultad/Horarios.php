<?php

namespace App\Models\CoordinadorFacultad;

use App\Enums\DiaSemana;
use App\Enums\TipoJornada;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Users\User;
use App\Models\Academico\CicloLectivo;
use App\Models\CoordinadorFacultad\GrupoHorario;
use App\Models\CoordinadorFacultad\SeccionHorario;

class Horarios extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'horario_id';
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo_lectivo',
        'dia',
        'hora_inicio',
        'hora_fin',
        'jornada',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'dia'        => DiaSemana::class,
            'jornada'    => TipoJornada::class,
            'creado_en'  => 'immutable_datetime',
        ];
    }

    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class, 'id_ciclo_lectivo', 'ciclo_lectivo_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'id');
    }

    public function gruposHorario(): HasMany
    {
        return $this->hasMany(GrupoHorario::class, 'id_horario', 'horario_id');
    }

    public function seccionesHorario(): HasMany
    {
        return $this->hasMany(SeccionHorario::class, 'id_horario', 'horario_id');
    }

    /** Duración en minutos */
    public function getDuracionMinutosAttribute(): int
    {
        return \Carbon\Carbon::parse($this->hora_inicio)
            ->diffInMinutes(\Carbon\Carbon::parse($this->hora_fin));
    }

    /** Etiqueta corta: "Lun 06:10-07:50" */
    public function getEtiquetaAttribute(): string
    {
        return sprintf(
            '%s %s-%s',
            $this->dia?->short() ?? '',
            substr($this->hora_inicio, 0, 5),
            substr($this->hora_fin, 0, 5)
        );
    }
}