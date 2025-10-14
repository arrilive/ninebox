<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table = 'tipos_usuarios';

    protected $fillable = [
        'tipo_nombre',
        'descripcion',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'tipo_usuario_id');
    }
}