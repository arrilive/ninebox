<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    protected $table = 'preguntas';
    protected $fillable = ['texto', 'categoria'];

    // categorías estándar
    public const CAT_DESEMPENO = 'desempeno';
    public const CAT_POTENCIAL = 'potencial';

    public function respuestas()
    {
        return $this->hasMany(Evaluacion::class, 'pregunta_id');
    }
}
