<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoIdentidad extends Model 
{
    protected $table = 'tipo_documento_identidad';
    protected $primaryKey = 'tipo_documento_id';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'nombre', 'formato_regex','formato'
    ];

    // relacion
    public function tipo_documento_identidad() {
        return $this->hasOne(User::class, 'id_tipo_documento');
    }

}