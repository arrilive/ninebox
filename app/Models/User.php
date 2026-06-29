<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\RolUsuario;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'user_name',
        'password',
        'correo',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'departamento_id',
        'tipo_usuario_id',
        'sucursal_id',
        'empresa_id',
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

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function scopeDeEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Relación: empleados del mismo departamento (solo tipo empleado).
     * Para jefes: retorna los empleados de su departamento.
     * Uso: $jefe->empleados
     */
    public function empleados()
    {
        return $this->hasMany(self::class, 'departamento_id', 'departamento_id')
                    ->where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['empleado'])
                    ->where('id', '!=', $this->id);
    }

    // ==================== HELPERS ====================
    
    public function rol(): RolUsuario
    {
        return RolUsuario::from($this->tipoUsuario->nombre);
    }

    public function esSuperadmin(): bool { return $this->rol() === RolUsuario::Superadmin; }
    public function esDueno(): bool      { return $this->rol() === RolUsuario::Dueno; }
    public function esJefe(): bool       { return $this->rol() === RolUsuario::Jefe; }
    public function esEmpleado(): bool   { return $this->rol() === RolUsuario::Empleado; }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function evaluacionActual()
    {
        return $this->hasOne(Rendimiento::class, 'usuario_id')
                    ->whereDate('created_at', today())
                    ->with('nineBox');
    }
}
