<?php

namespace App\Models\Users;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'id_tipo_documento', 'documento_identidad', 'fecha_nacimiento', 'genero', 'estado_civil', 'celular', 'direccion', 'estado'])]
#[Hidden(['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_tipo_documento', 
        'documento_identidad', 
        'fecha_nacimiento', 
        'genero', 
        'estado_civil', 
        'celular', 
        'direccion', 
        'estado'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];
    
    // Relaciones
    public function roles() {
        return $this->belongsToMany(Role::class, 'usuario_rol_id', 'id_usuario', 'id_rol');
    }
    
    public function alumno() {
        return $this->hasOne(Alumnos::class, 'id_usuario');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}