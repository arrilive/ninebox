<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Encuesta extends Model
{
    protected $table = 'encuestas';
    protected $fillable = [
        'usuario_id','total_desempeno','total_potencial','puntaje_final',
        'ninebox_id','activa','feedback_publico','notas_privadas'
    ];
    protected $casts = [
        'activa' => 'bool',
        'total_desempeno' => 'integer',
        'total_potencial' => 'integer',
        'puntaje_final' => 'float',
    ];

    // Relaciones
    public function usuario()    { return $this->belongsTo(Usuario::class, 'usuario_id'); }
    public function respuestas() { return $this->hasMany(Evaluacion::class, 'encuesta_id'); }
    public function ninebox()    { return $this->belongsTo(NineBox::class, 'ninebox_id'); }

    // Scopes útiles
    public function scopeBorrador($q) { return $q->where('activa', true); }
    public function scopeCerrada($q)  { return $q->where('activa', false); }

    // Cálculo de totales (desempeño/potencial y final)
    public function recalcularTotales(): self
    {
        $tot = $this->respuestas()
            ->join('preguntas as p', 'p.id', '=', 'evaluaciones.pregunta_id')
            ->selectRaw("
                SUM(CASE WHEN p.categoria = 'desempeno' THEN evaluaciones.puntaje ELSE 0 END) as td,
                SUM(CASE WHEN p.categoria = 'potencial' THEN evaluaciones.puntaje ELSE 0 END) as tp,
                SUM(evaluaciones.puntaje) as total
            ")
            ->first();

        $this->total_desempeno = (int) ($tot->td ?? 0);
        $this->total_potencial = (int) ($tot->tp ?? 0);
        $this->puntaje_final   = (float) ($tot->total ?? 0);

        return $this;
    }

    // Resolver cuadrante vía reglas y asignar ninebox_id
    public function resolverCuadrante(): self
    {
        $regla = ReglaNinebox::resolver($this->total_desempeno ?? 0, $this->total_potencial ?? 0);
        $this->ninebox_id = $regla?->ninebox_id;
        return $this;
    }
}
