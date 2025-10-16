<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';

    // Relación: empleados de este departamento
    public function empleados()
    {
        return $this->hasMany(\App\Models\User::class, 'departamento_id');
    }

    /**
     * Relación: jefe del departamento.
     * Se asume que la tabla 'departamentos' tiene campo 'jefe_id' que referencia usuarios.id
     */
    public function jefe()
    {
        return $this->belongsTo(\App\Models\User::class, 'jefe_id');
    }
}
