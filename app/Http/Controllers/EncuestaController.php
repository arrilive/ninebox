<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Evaluacion;
use Carbon\Carbon;

class EncuestaController extends Controller
{
    /**
     * GET /encuestas/empleados?anio=YYYY&mes=M
     * Lista empleados del jefe con filtros: evaluados, en_proceso, no_iniciados.
     */
    public function listaEmpleados(Request $request)
    {
        $user = $request->user(); // Jefe / Superusuario (ya validado por middleware)
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        // Total de preguntas (esperado 10)
        $totalPreg = (int) DB::table('preguntas')->count();

        // Empleados del jefe (según tu relación definida en User)
        // Si es superusuario, puedes listar todos; si es jefe, solo su departamento.
        if (strtolower($user->tipoUsuario->tipo_nombre ?? '') === 'superusuario') {
            $empleadosBase = User::query()
                ->whereHas('tipoUsuario', fn($q) => $q->whereRaw('LOWER(tipo_nombre) = ?', ['empleado']))
                ->get();
        } else {
            $empleadosBase = $user->empleados()->get(); // usa tu relación $jefe->empleados()
        }

        // Trae encuestas del periodo (por created_at en año/mes)
        $encuestasPeriodo = Encuesta::query()
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->whereIn('usuario_id', $empleadosBase->pluck('id'))
            ->get()
            ->keyBy('usuario_id');

        // Progreso por encuesta: respuestas contadas
        $progresos = [];
        if ($encuestasPeriodo->isNotEmpty()) {
            $resps = Evaluacion::query()
                ->select('encuesta_id', DB::raw('COUNT(*) as contestadas'))
                ->whereIn('encuesta_id', $encuestasPeriodo->pluck('id'))
                ->groupBy('encuesta_id')
                ->get()
                ->keyBy('encuesta_id');

            foreach ($encuestasPeriodo as $enc) {
                $progresos[$enc->usuario_id] = (int) ($resps[$enc->id]->contestadas ?? 0);
            }
        }

        // Clasifica estado: evaluado (cerrada), en_proceso (1..9), no_iniciado (0 o sin encuesta)
        $empleados = $empleadosBase->map(function (User $e) use ($encuestasPeriodo, $progresos, $totalPreg) {
            $enc = $encuestasPeriodo->get($e->id);
            $contestadas = (int)($progresos[$e->id] ?? 0);

            if ($enc && $enc->activa === false) {
                $estado = 'evaluado';
            } elseif ($contestadas > 0 && $contestadas < $totalPreg) {
                $estado = 'en_proceso';
            } elseif ($contestadas >= $totalPreg) {
                // Si por alguna razón quedó activa con 10/10, la tratamos como evaluada
                $estado = 'evaluado';
            } else {
                $estado = 'no_iniciado';
            }

            return [
                'id' => $e->id,
                'nombre' => trim("{$e->nombre} {$e->apellido_paterno} {$e->apellido_materno}"),
                'departamento_nombre' => optional($e->departamento)->nombre_departamento ?? 'Sin departamento',
                'estado' => $estado,
                'progreso' => "{$contestadas}/{$totalPreg}",
            ];
        });

        // Filtros opcionales (?filtro=evaluado|en_proceso|no_iniciado)
        $filtro = $request->query('filtro');
        if (in_array($filtro, ['evaluado', 'en_proceso', 'no_iniciado'])) {
            $empleados = $empleados->where('estado', $filtro)->values();
        }

        // KPIs
        $kpi_total       = $empleados->count();
        $kpi_evaluados   = $empleados->where('estado', 'evaluado')->count();
        $kpi_en_proceso  = $empleados->where('estado', 'en_proceso')->count();
        $kpi_no_iniciado = $empleados->where('estado', 'no_iniciado')->count();

        return view('encuestas.empleados-index', [
            'usuario'          => $user,
            'anio'             => $anio,
            'mes'              => $mes,
            'empleados'        => $empleados,
            'totalPreguntas'   => $totalPreg,
            'kpi_total'        => $kpi_total,
            'kpi_evaluados'    => $kpi_evaluados,
            'kpi_en_proceso'   => $kpi_en_proceso,
            'kpi_no_iniciado'  => $kpi_no_iniciado,
        ]);
    }

    /**
     * GET /encuestas/{empleado}?anio&mes
     * Muestra la encuesta (crea borrador si no existe) + preguntas y respuestas.
     */
    public function show(Request $request, int $empleado)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        // Seguridad: si es Jefe, que solo pueda ver a sus empleados
        if (strtolower($user->tipoUsuario->tipo_nombre ?? '') !== 'superusuario') {
            $esDeMiDepto = $user->empleados()->where('id', $empleado)->exists();
            abort_unless($esDeMiDepto, 403);
        }

        // Encuesta del periodo o crear borrador activa
        $encuesta = Encuesta::query()
            ->where('usuario_id', $empleado)
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->first();

        if (!$encuesta) {
            $encuesta = Encuesta::create([
                'usuario_id' => $empleado,
                'activa'     => true,
                // created_at define periodo (anio/mes), no dependemos de columnas extra
            ]);
        }

        // Preguntas (10)
        $preguntas = Pregunta::orderBy('categoria')->orderBy('id')->get();

        // Respuestas existentes indexadas por pregunta_id
        $respuestas = Evaluacion::query()
            ->where('encuesta_id', $encuesta->id)
            ->get()
            ->keyBy('pregunta_id');

        // Progreso
        $totalPreg = (int) $preguntas->count();
        $contestadas = (int) $respuestas->count();

        return view('encuestas.encuesta-show', [
            'usuario'        => $user,
            'anio'           => $anio,
            'mes'            => $mes,
            'empleadoId'     => $empleado,
            'encuesta'       => $encuesta,
            'preguntas'      => $preguntas,
            'respuestas'     => $respuestas, // para prellenar
            'totalPreguntas' => $totalPreg,
            'contestadas'    => $contestadas,
        ]);
    }

    /**
     * POST /encuestas/{empleado}?anio&mes
     * Guarda respuestas (upsert por pregunta). Si quedan 10/10, recalcula totales, resuelve 9-box y cierra.
     */
    public function submit(Request $request, int $empleado)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        // Seguridad (jefe sólo su gente)
        if (strtolower($user->tipoUsuario->tipo_nombre ?? '') !== 'superusuario') {
            $esDeMiDepto = $user->empleados()->where('id', $empleado)->exists();
            abort_unless($esDeMiDepto, 403);
        }

        // Encuesta del periodo (debe existir; si no, crear)
        $encuesta = Encuesta::query()
            ->where('usuario_id', $empleado)
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->first();

        if (!$encuesta) {
            $encuesta = Encuesta::create([
                'usuario_id' => $empleado,
                'activa'     => true,
            ]);
        }

        // Validación básica: respuestas.*.pregunta_id y respuestas.*.puntaje (0..5 por ejemplo)
        $data = $request->validate([
            'respuestas'               => ['required', 'array'],
            'respuestas.*.pregunta_id' => ['required', 'integer', 'exists:preguntas,id'],
            'respuestas.*.puntaje'     => ['required', 'integer', 'min:0', 'max:5'],
            'respuestas.*.comentario'  => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($encuesta, $data) {
            foreach ($data['respuestas'] as $r) {
                Evaluacion::updateOrCreate(
                    ['encuesta_id' => $encuesta->id, 'pregunta_id' => (int)$r['pregunta_id']],
                    ['puntaje' => (int)$r['puntaje'], 'comentario' => $r['comentario'] ?? null]
                );
            }
        });

        // Recalcular progreso
        $totalPreg = (int) DB::table('preguntas')->count();
        $contestadas = (int) Evaluacion::where('encuesta_id', $encuesta->id)->count();

        // Si completó 10/10 -> cerrar, recalcular totales y resolver 9-box
        if ($contestadas >= $totalPreg) {
            $encuesta->recalcularTotales()->resolverCuadrante();
            $encuesta->activa = false; // cerrada
            $encuesta->save();
        } else {
            // Borrador sigue activo
            if ($encuesta->activa === false) {
                $encuesta->activa = true;
                $encuesta->save();
            }
        }

        return redirect()
            ->route('encuestas.show', ['empleado' => $empleado, 'anio' => $anio, 'mes' => $mes])
            ->with('status', $contestadas >= $totalPreg
                ? 'Encuesta enviada y cerrada correctamente.'
                : 'Borrador guardado. Puedes continuar después.'
            );
    }
}
    