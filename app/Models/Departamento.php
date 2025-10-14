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
     * (si no existe ese campo, omite esta relación o ajusta según tu esquema).
     */
    public function jefe()
    {
        // El departamento tiene un jefe que es un usuario con id = jefe_id
        return $this->hasOne(\App\Models\User::class, 'id', 'jefe_id');
    }
}
