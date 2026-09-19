<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne; 
Use App\Models\Users\Users;

class RegionalActivo extends Model 
{
    protected $table = 'regional_activo';
    protected $primaryKey = 'regional_activo_id';
    public $timestamps = false;

    protected $fillable = [
        'sede'
    ];

    // relacion
    public function user(): HasOne {
        return $this->hasOne(User::class, 'id_regional_activo', 'regional_activo_id');
    }

}