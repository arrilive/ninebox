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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    public function getAuthIdentifierName()
    {
        return 'correo';
    }

    // ==================== RELACIONES ====================
    
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

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Relación: empleados del mismo departamento (solo tipo empleado).
     * Para jefes: retorna los empleados de su departamento.
     * Uso: $jefe->empleados
     */
    public function empleados()
    {
        return $this->hasMany(self::class, 'departamento_id', 'departamento_id')
                    ->where('tipo_usuario_id', 3)
                    ->where('id', '!=', $this->id);
    }

    // ==================== HELPERS ====================
    
    public function esJefe()
    {
        return $this->tipoUsuario->tipo_nombre === 'Jefe';
    }

    public function esSuperusuario()
    {
        return $this->tipoUsuario->tipo_nombre === 'Superadmin';
    }

    public function esEmpleado()
    {
        return $this->tipoUsuario->tipo_nombre === 'Empleado';
    }

    public function esDueno()
    {
        return $this->tipoUsuario->tipo_nombre === 'Dueño';
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function evaluacionActual()
    {
        return $this->hasOne(Rendimiento::class, 'usuario_id')
                    ->whereDate('created_at', today())
                    ->with('nineBox');
    }
}
