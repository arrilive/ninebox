<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = ['nombre', 'slug', 'activa'];

    protected $casts = ['activa' => 'boolean'];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'empresa_id');
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'empresa_id');
    }
}
