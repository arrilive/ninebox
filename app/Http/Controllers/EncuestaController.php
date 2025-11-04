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
     * 
     * CAMBIOS REALIZADOS:
     * - Siempre carga TODOS los empleados del período, ignorando el filtro server-side.
     *   El JS maneja el filtrado y actualización de estados por borradores (sessionStorage).
     * - Estados server-side: Solo 'evaluado' (encuesta cerrada) o 'no_iniciado' (cualquier cosa no cerrada).
     *   No hay 'en_proceso' server-side, ya que los drafts puros no están en DB; JS lo detecta.
     * - KPIs: Calculados server-side basados SOLO en DB (en_proceso=0 inicial). JS los recalcula
     *   incluyendo drafts, pero solo cuando se muestra el sidebar ("Todos").
     * - Si hay encuesta activa con respuestas parciales en DB (de submits previos), se marca como 'no_iniciado'
     *   pero JS lo ajustará si hay draft más reciente. Esto es consistente.
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

        // Clasifica estado: SOLO 'evaluado' (cerrada) o 'no_iniciado' (abierta/parcial/sin encuesta)
        // JS manejará 'en_proceso' vía drafts en sessionStorage.
        $empleados = $empleadosBase->map(function (User $e) use ($encuestasPeriodo, $progresos, $totalPreg) {
            $enc = $encuestasPeriodo->get($e->id);
            $contestadas = (int)($progresos[$e->id] ?? 0);

            if ($enc && $enc->activa === false) {
                $estado = 'evaluado';
                $progreso = "{$contestadas}/{$totalPreg}"; // Debería ser full si cerrada
            } else {
                // Cualquier cosa no cerrada: 'no_iniciado' (incluye parciales en DB o sin encuesta)
                // JS chequeará drafts y lo cambiará a 'en_proceso' si filled > 0.
                $estado = 'no_iniciado';
                $progreso = $enc ? "{$contestadas}/{$totalPreg}" : "0/{$totalPreg}";
            }

            return [
                'id' => $e->id,
                'nombre' => trim("{$e->nombre} {$e->apellido_paterno} {$e->apellido_materno}"),
                'departamento_nombre' => optional($e->departamento)->nombre_departamento ?? 'Sin departamento',
                'estado' => $estado,
                'progreso' => $progreso,
            ];
        });

        // ELIMINADO: No filtrar server-side. JS lo hace en applyStateFusion() basado en FILTRO.
        // $filtro = $request->query('filtro');
        // if (in_array($filtro, ['evaluado', 'en_proceso', 'no_iniciado'])) {
        //     $empleados = $empleados->where('estado', $filtro)->values();
        // }

        // KPIs server-side: Basados en DB SOLO (en_proceso=0, ya que drafts son client-side)
        // JS los recalculará correctamente al cargar (incluyendo drafts).
        $kpi_total       = $empleadosBase->count(); // Total real de empleados
        $kpi_evaluados   = $empleados->where('estado', 'evaluado')->count();
        $kpi_en_proceso  = 0; // Server no sabe de drafts; JS ajustará
        $kpi_no_iniciado = $kpi_total - $kpi_evaluados; // Incluye potenciales 'en_proceso'

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