<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'user_name',
        'password',
        'correo',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'departamento_id',
        'tipo_usuario_id',
        'sucursal_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // <-- CORREGIDO: propiedad $casts en lugar de method
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    // Para que Breeze use 'correo' en lugar de 'email' (si ya lo adaptaste)
    public function getAuthIdentifierName()
    {
        return 'correo'; // o 'user_name' si prefieres login con username
    }

    // Relaciones
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function tipoUsuario()
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_id');
    }

    public function rendimientos()
    {
        return $this->hasMany(Rendimiento::class, 'usuario_id');
    }

    // Helper: verifica si es jefe
    public function esJefe()
    {
        return $this->tipo_usuario_id == 2;
    }

    // Helper: verifica si es superusuario
    public function esSuperusuario()
    {
        return $this->tipo_usuario_id == 1;
    }

    /**
     * Relación auxiliar: obtener empleados que pertenecen al mismo departamento del jefe.
     * Filtra por tipo_usuario_id = 3 (empleado).
     * Uso: $jefe->empleados
     */
    public function empleados()
    {
        // Si el usuario no tiene departamento_id, devolvemos una relación vacía mediante whereNull/where impossible.
        // Pero Eloquent permite devolver hasMany con la misma columna departamento_id.
        return $this->hasMany(self::class, 'departamento_id', 'departamento_id')
                    ->where('tipo_usuario_id', 3);
    }
}
