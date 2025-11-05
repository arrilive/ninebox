<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rendimiento extends Model
{
    protected $table = 'rendimientos';

    // Tabla sin PK autoincremental
    public $incrementing = false;
    protected $primaryKey = null;

    // Solo usamos created_at; no hay updated_at
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = ['usuario_id','ninebox_id','comentario'];

    protected $casts = [
        'usuario_id' => 'integer',
        'ninebox_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
    public function nineBox() { return $this->belongsTo(NineBox::class, 'ninebox_id'); }
}