<?php
namespace App\Services;

use App\Models\Encuesta;
use App\Models\Evaluacion;
use App\Models\Rendimiento;
use App\Models\ReglaNinebox;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EvaluacionService
{
    /**
     * Guarda o actualiza las respuestas individuales de una encuesta.
     * Si el puntaje es null, elimina la respuesta existente.
     * Debe ejecutarse dentro de una transacción.
     */
    public function guardarRespuestas(Encuesta $encuesta, array $respuestas): void
    {
        foreach ($respuestas as $r) {
            $preguntaId = (int) $r['pregunta_id'];
            $puntaje    = $r['puntaje'] ?? null;

            if ($puntaje === null) {
                // Si el usuario desmarcó la respuesta, la eliminamos
                Evaluacion::where('encuesta_id', $encuesta->id)
                    ->where('pregunta_id', $preguntaId)
                    ->delete();
                continue;
            }

            DB::table('evaluaciones')->updateOrInsert(
                [
                    'encuesta_id' => $encuesta->id,
                    'pregunta_id' => $preguntaId,
                ],
                [
                    'puntaje'    => (int) $puntaje,
                    'comentario' => $r['comentario'] ?? null,
                    'created_at' => now(),  
                    'updated_at' => now(),   
                ]
            );
        }
    }

    /**
     * Calcula los totales de desempeño y potencial sumando los puntajes
     * de las evaluaciones ya guardadas en la encuesta.
     * Retorna ['desempeno' => int, 'potencial' => int]
     */
    public function calcularTotales(Encuesta $encuesta): array
    {
        $totales = Evaluacion::query()
            ->join('preguntas','preguntas.id','=','evaluaciones.pregunta_id')
            ->where('evaluaciones.encuesta_id', $encuesta->id)
            ->selectRaw("
                SUM(CASE WHEN preguntas.categoria = 'desempeno' THEN evaluaciones.puntaje ELSE 0 END) AS total_desempeno,
                SUM(CASE WHEN preguntas.categoria = 'potencial'  THEN evaluaciones.puntaje ELSE 0 END) AS total_potencial
            ")
            ->first();

        return [
            'desempeno' => (int)($totales->total_desempeno ?? 0),
            'potencial' => (int)($totales->total_potencial ?? 0),
        ];
    }

    /**
     * Busca la regla Nine-Box que corresponde a los totales dados.
     * Lanza \RuntimeException si no existe regla para esos valores.
     */
    public function resolverCuadrante(int $totalDesempeno, int $totalPotencial): ReglaNinebox
    {
        $regla = ReglaNinebox::where('min_desempeno', '<=', $totalDesempeno)
            ->where('max_desempeno', '>=', $totalDesempeno)
            ->where('min_potencial', '<=', $totalPotencial)
            ->where('max_potencial', '>=', $totalPotencial)
            ->first();

        if (!$regla) {
            throw new \RuntimeException("No existe regla para desempeño={$totalDesempeno} y potencial={$totalPotencial}. Revisa reglas_ninebox.");
        }

        return $regla;
    }

    /**
     * Cierra la encuesta: graba totales, ninebox_id, cerrada_en, anio, mes,
     * evaluador_id. No crea el Rendimiento (eso es responsabilidad de registrarRendimiento).
     */
    public function cerrarEncuesta(
        Encuesta $encuesta,
        int $totalDesempeno,
        int $totalPotencial,
        ReglaNinebox $regla,
        int $anio,
        int $mes,
        User $evaluador
    ): Encuesta {
        $encuesta->activa          = false;
        $encuesta->enviada_en      = now();
        $encuesta->cerrada_en      = now();
        $encuesta->ninebox_id      = (int)$regla->ninebox_id;
        $encuesta->total_desempeno = $totalDesempeno;
        $encuesta->total_potencial = $totalPotencial;
        $encuesta->anio            = $anio;
        $encuesta->mes             = $mes;
        $encuesta->evaluador_id    = $evaluador->id;
        $encuesta->jefe_id         = $evaluador->id;

        $encuesta->save();

        return $encuesta;
    }

    /**
     * Crea o reemplaza el Rendimiento del periodo para el usuario evaluado.
     * El Rendimiento debe tener: usuario_id, ninebox_id, encuesta_id, anio, mes.
     */
    public function registrarRendimiento(Encuesta $encuesta): Rendimiento
    {
        $usuarioId = $encuesta->usuario_id;
        $anio = $encuesta->anio;
        $mes = $encuesta->mes;

        Rendimiento::where('usuario_id', $usuarioId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->delete();

        $r = new Rendimiento([
            'usuario_id'  => $usuarioId,
            'ninebox_id'  => (int)$encuesta->ninebox_id,
            'encuesta_id' => $encuesta->id,
            'anio'        => $anio,
            'mes'         => $mes,
        ]);
        $r->save();

        return $r;
    }
}
