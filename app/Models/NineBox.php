<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NineBox extends Model
{
    protected $table = 'nine_box';

    protected $fillable = ['nombre','posicion','descripcion'];

    public function rendimientos(){ return $this->hasMany(Rendimiento::class,'ninebox_id'); }
    public function encuestas(){ return $this->hasMany(Encuesta::class,'ninebox_id'); }

    protected static function grid(): array {
        $presets = config('ninebox.presets');
        $active  = config('ninebox.active_preset', 'SECOND_IMAGE_ORDER');
        return $presets[$active] ?? $presets['SECOND_IMAGE_ORDER'];
    }

    /** id -> ['row'=>1..3, 'col'=>1..3] */
    public static function posMap(): array {
        $grid = static::grid(); $map = [];
        foreach ($grid as $rIdx => $row)
            foreach ($row as $cIdx => $id)
                $map[(int)$id] = ['row'=>$rIdx+1, 'col'=>$cIdx+1];
        return $map;
    }

    /** fila/col -> id */
    public static function idFromRowCol(int $row, int $col): ?int {
        $g = static::grid();
        return ($row>=1 && $row<=3 && $col>=1 && $col<=3) ? (int)$g[$row-1][$col-1] : null;
    }

    // Accesores útiles si en algún momento pintas por modelo
    public function getRowAttribute(){ $k=$this->posicion ?? $this->id; return static::posMap()[$k]['row'] ?? null; }
    public function getColAttribute(){ $k=$this->posicion ?? $this->id; return static::posMap()[$k]['col'] ?? null; }
}