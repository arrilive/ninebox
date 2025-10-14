<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rendimiento extends Model
{
    protected $table = 'rendimientos';
    
    // No usa incrementing ID porque la PK es compuesta
    public $incrementing = false;
    
    // La PK compuesta
    protected $primaryKey = ['usuario_id', 'fecha'];

    // Desactivar timestamps automáticos (solo tienes created_at)
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'ninebox_id',
        'fecha',
        'comentario',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con cuadrante de 9-box
    public function nineBox()
    {
        return $this->belongsTo(NineBox::class, 'ninebox_id');
    }
}