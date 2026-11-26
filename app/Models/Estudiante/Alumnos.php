<?php

namespace App\Models\Estudiante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

Use App\Models\Users\User;
Use App\Models\Estudiante\AlumnoDocumentos;
Use App\Models\Estudiante\Carreras;
Use App\Models\Academico\BeneficioEstudiante;
Use App\Models\Academico\CargoEstudiante;
use App\Models\Estudiante\RequisitosEgreso;
use App\Models\Academico\ProcesoGraduacion;
use App\Enums\EstadoBeneficio;
use App\Enums\EstadoEstudiante;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Alumnos extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'alumno_id';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario', 
        'id_carrera', 
        'codigo_estudiante',
        'correo_institucional', 
        'estado_carrera',
        'foto_carnet', 
        'foto_carnet_validada_at', 
        'id_alumno_origen',
    ];

    protected function casts(): array
    {
        return [
            'foto_carnet_validada_at' => 'datetime',
            'estado_carrera'          => 'string',
        ];
    }

    // relaciones
    public function documentos(): HasOne {
        return $this->hasOne(AlumnoDocumentos::class, 'id_alumno', 'alumno_id');
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carreras::class, 'id_carrera', 'carrera_id');
    }

    public function requisitosEgreso(): HasMany
    {
        return $this->hasMany(RequisitosEgreso::class, 'id_alumno', 'alumno_id');
    }

    public function procesosGraduacion(): HasMany
    {
        return $this->hasMany(ProcesoGraduacion::class, 'id_alumno', 'alumno_id');
    }

    public function procesoGraduacionActivo(): HasOne
    {
        return $this->hasOne(ProcesoGraduacion::class, 'id_alumno', 'alumno_id')
            ->where('es_activo', true)
            ->latestOfMany('proceso_id');
    }

    public function beneficios(): HasMany
    {
        return $this->hasMany(BeneficioEstudiante::class, 'id_alumno', 'alumno_id');
    }

    public function beneficioCicloActual(): HasOne
    {
        return $this->hasOne(BeneficioEstudiante::class, 'id_alumno', 'alumno_id')
            ->where('estado', 'ACTIVO')
            ->latestOfMany('beneficio_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(CargoEstudiante::class, 'id_alumno', 'alumno_id');
    }

    // nuevos helpers
    public function origen(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class, 'id_alumno_origen', 'alumno_id');
    }

    /**
     * Accessor al enum, sin cambiar el cast original.
     * Uso: $alumno->estado_enum === EstadoEstudiante::ACTIVO
     */
    protected function estadoEnum(): Attribute
    {
        return Attribute::make(
            get: fn () => EstadoEstudiante::tryFrom($this->estado_carrera ?? ''),
        );
    }

    /**
     * Helper booleano para no repetir comparaciones.
     */
    public function estaActivo(): bool
    {
        return $this->estado_carrera === EstadoEstudiante::ACTIVO->value;
    }

    public function estaGraduado(): bool
    {
        return $this->estado_carrera === EstadoEstudiante::GRADUADO->value;
    }

    public function puedeReactivarse(): bool
    {
        return $this->estado_carrera === EstadoEstudiante::CAMBIO_VOLUNTARIO->value;
    }

    /**
     * Expedientes derivados (si este expediente fue el origen de un cambio).
     */
    public function derivados(): HasMany
    {
        return $this->hasMany(Alumnos::class, 'id_alumno_origen', 'alumno_id');
    }

    /**
     * Documentos del expediente. Si este expediente nació de un cambio,
     * usa los documentos del expediente origen.
     */
    public function documentosEfectivos(): HasOne
    {
        // Si tiene documentos propios, los usa; si no, los del origen.
        // Esto se resuelve en PHP porque Eloquent no hace fallback condicional.
        return $this->hasOne(AlumnoDocumentos::class, 'id_alumno', 'alumno_id');
    }

    /**
     * Helper: devuelve los documentos (propios o heredados del origen).
     */
    public function getDocumentosHeredadosAttribute(): ?AlumnoDocumentos
    {
        // Caso 1: el expediente tiene documentos propios → los devuelve
        if ($this->documentos) {
            return $this->documentos;
        }

        // Caso 2: el expediente es derivado → busca los del origen
        if ($this->id_alumno_origen) {
            return AlumnoDocumentos::where('id_alumno', $this->id_alumno_origen)->first();
        }

        // Caso 3: sin documentos propios ni origen → null
        return null;
    }

    /**
     * Devuelve el expediente activo del usuario (o el graduado único si no hay activo).
     */
    public static function activoDeUsuario(int $userId): ?self
    {
        // 1. Prioridad: expediente en sesión
        $alumnoIdSesion = session('alumno_id_activo');
        if ($alumnoIdSesion) {
            $alumno = self::where('alumno_id', $alumnoIdSesion)
                ->where('id_usuario', $userId)
                ->first();
            if ($alumno) {
                return $alumno;
            }
        }

        // 2. Fallback: el ACTIVO
        $activo = self::where('id_usuario', $userId)
            ->where('estado_carrera', EstadoEstudiante::ACTIVO->value)
            ->first();

        if ($activo) {
            return $activo;
        }

        // 3. Si no hay ACTIVO, y hay un único GRADUADO, devolverlo
        $graduados = self::where('id_usuario', $userId)
            ->where('estado_carrera', EstadoEstudiante::GRADUADO->value)
            ->get();

        return $graduados->count() === 1 ? $graduados->first() : null;
    }

    /**
     * Scope: expedientes activos.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_carrera', EstadoEstudiante::ACTIVO->value);
    }

    /**
     * Scope: expediente activo de un usuario (debe haber máximo uno).
     */
    public function scopeActivoDeUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('id_usuario', $userId)->where('estado_carrera', EstadoEstudiante::ACTIVO->value);
    }


    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado_carrera', 'PENDIENTE');
    }

    public function scopeConSolicitudCompleta(Builder $query): Builder
    {
        return $query->with([
            'usuario:id,name,email,documento_identidad,id_tipo_documento,fecha_nacimiento,genero,estado_civil,celular,direccion,id_regional_activo,estado,created_at',
            'usuario.tipodocumentoidentidad',
            'usuario.regionalactivo',
            'carrera.facultad',
            'documentos',
        ]);
    }

    public function beneficioActivo(): ?BeneficioEstudiante
    {
        return $this->beneficios->firstWhere('estado', EstadoBeneficio::ACTIVO);
    }
    // ========================================================
    // para profile carnet
    // ========================================================
    /** URL de la foto del carnet (subida específicamente) */
    public function getFotoCarnetUrlAttribute(): ?string
    {
        return $this->foto_carnet
            ? route('estudiante.foto', absolute: false)  // ← ruta relativa
            : null;
    }

    public function getFotoExpedienteUrlAttribute(): ?string
    {
        if (!$this->documentos_heredados?->fotografia_personal) {
            return null;
        }

        return route('estudiante.foto', absolute: false);
    }

    /** URL final de la foto a mostrar en el carnet */
    public function getFotoCarnetMostrarAttribute(): ?string
    {
        return $this->foto_carnet_url ?? $this->foto_expediente_url;
    }

    /** Indica si la foto mostrada es la del carnet (subida específicamente) */
    public function getFotoCarnetEsSubidaAttribute(): bool
    {
        return $this->foto_carnet_url !== null;
    }
    
        /** Año de vigencia del carnet (año actual) */
    public function getAnioVigenciaCarnetAttribute(): int
    {
        return now()->year;
    }
}
