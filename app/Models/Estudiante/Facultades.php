<?php

namespace App\Models\Estudiante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Estudiante\Carreras;

class Facultades Extends Model {

    protected $table = 'facultades';
    protected $primaryKey = 'facultad_id';
    public $timestamps = false;

    // campos de la tabla
    protected $fillable = [
        'nombre'
    ];

    // relaciones una facultad puede tener muchas carreras 
     public function carreras(): HasMany
    {
        return $this->hasMany(Carreras::class, 'id_facultad', 'facultad_id');
    }

    // --Helpers --
    public function tieneCarreras(): bool
    {
        return $this->carreras()->exists();
    }
}