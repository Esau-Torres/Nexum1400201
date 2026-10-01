<?php 

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'rol_id';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    // Constantes asi no se equivocan al escribir en el middelware mas vos leandro mudo
    public const SUPER_ADMIN          = 'SUPER_ADMIN';
    public const DIRECTIVO            = 'DIRECTIVO';
    public const ADMIN_ACADEMICO      = 'ADMIN_ACADEMICO';
    public const COORDINADOR_FACULTAD = 'COORDINADOR_FACULTAD';
    public const CAJERO               = 'CAJERO';
    public const ADMIN_FINANCIERO     = 'ADMIN_FINANCIERO';
    public const DOCENTE              = 'DOCENTE';
    public const ESTUDIANTE           = 'ESTUDIANTE';

    // relacion muchos a muchos
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_rol', 'id_rol', 'id_usuario');
    }
}