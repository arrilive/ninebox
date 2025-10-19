<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rendimiento extends Model
{
    protected $table = 'rendimientos';

    // Ahora usamos PK auto-incremental
    protected $primaryKey = 'id';
    public $incrementing = true;

    // Desactivar timestamps automáticos de updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'ninebox_id',
        'comentario',
    ];


    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    // Relación con cuadrante de 9-box
    public function nineBox()
    {
        return $this->belongsTo(\App\Models\NineBox::class, 'ninebox_id');
    }
}