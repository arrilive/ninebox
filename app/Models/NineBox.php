<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NineBox extends Model
{
    protected $table = 'nine_box';

    protected $fillable = [
        'nombre',
        'posicion',
        'descripcion',
    ];

    // Relación con rendimientos
    public function rendimientos()
    {
        return $this->hasMany(Rendimiento::class, 'ninebox_id');
    }
}