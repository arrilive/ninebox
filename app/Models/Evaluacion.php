<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    protected $table = 'evaluaciones';
    public $incrementing = false;        // PK compuesta en DB
    protected $primaryKey = null;        
    protected $fillable = ['encuesta_id','pregunta_id','puntaje','comentario'];
    protected $casts = ['puntaje' => 'integer'];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }
}
