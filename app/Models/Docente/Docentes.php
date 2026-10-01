<?php

namespace App\Models\Docente;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Docentes extends Model
{
    protected $table = 'docentes';
    protected $primaryKey = 'docente_id';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'codigo_empleado',
        'correo_institucional',
        'estado',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}