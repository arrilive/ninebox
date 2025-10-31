<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglaNinebox extends Model
{
    protected $table = 'reglas_ninebox';
    protected $fillable = [
        'min_desempeno','max_desempeno','min_potencial','max_potencial',
        'ninebox_id','etiqueta','activo'
    ];
    protected $casts = ['activo' => 'bool'];

    public function ninebox()
    {
        return $this->belongsTo(NineBox::class, 'ninebox_id');
    }

    // Helper para resolver una pareja (desempeño, potencial)
    public static function resolver(int $td, int $tp): ?self
    {
        return static::query()
            ->where('activo', true)
            ->whereRaw('? BETWEEN min_desempeno AND max_desempeno', [$td])
            ->whereRaw('? BETWEEN min_potencial AND max_potencial', [$tp])
            ->first();
    }
}
