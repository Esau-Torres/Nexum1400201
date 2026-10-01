<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne; 

class TipoDocumentoIdentidad extends Model 
{
    protected $table = 'tipo_documento_identidad';
    protected $primaryKey = 'tipo_documento_id';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'nombre', 'formato_regex','formato'
    ];

    // relacion
    public function user(): HasOne {
        return $this->hasOne(User::class, 'id_tipo_documento', 'tipo_documento_id');
    }

}