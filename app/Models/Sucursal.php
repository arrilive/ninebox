<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = ['nombre_sucursal', 'direccion', 'ciudad', 'empresa_id'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }
}
