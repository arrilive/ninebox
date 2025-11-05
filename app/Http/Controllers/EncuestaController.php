<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Evaluacion;
use App\Models\Rendimiento;

class EncuestaController extends Controller
{
    /**
     * GET /encuestas/empleados?anio=YYYY&mes=M
     */
    public function listaEmpleados(Request $request)
    {
        $user = $request->user(); // Jefe / Superusuario
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        // Total de preguntas (esperado 10)
        $totalPreg = (int) DB::table('preguntas')->count();

        // Empleados según rol
        if (strtolower($user->tipoUsuario->tipo_nombre ?? '') === 'superusuario') {
            $empleadosBase = User::query()
                ->whereHas('tipoUsuario', fn($q) => $q->whereRaw('LOWER(tipo_nombre) = ?', ['empleado']))
                ->get();
        } else {
            // relación $user->empleados() debe existir
            $empleadosBase = $user->empleados()->get();
        }

        // Encuestas del periodo
        $encuestasPeriodo = Encuesta::query()
            ->whereIn('usuario_id', $empleadosBase->pluck('id'))
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->get()
            ->keyBy('usuario_id');

        // Progreso por encuesta
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

        // Estado básico server-side
        $empleados = $empleadosBase->map(function (User $e) use ($encuestasPeriodo, $progresos, $totalPreg) {
            $enc = $encuestasPeriodo->get($e->id);
            $contestadas = (int)($progresos[$e->id] ?? 0);

            if ($enc && $enc->activa === false) {
                $estado = 'evaluado';
                $progreso = "{$contestadas}/{$totalPreg}";
            } else {
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

        // KPIs
        $kpi_total       = $empleadosBase->count();
        $kpi_evaluados   = $empleados->where('estado', 'evaluado')->count();
        $kpi_en_proceso  = 0;
        $kpi_no_iniciado = $kpi_total - $kpi_evaluados;

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

        // Seguridad: Jefe sólo su gente
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
            ]);
        }

        // Preguntas (ordenadas por categoría)
        $preguntas = Pregunta::orderBy('categoria')->orderBy('id')->get();

        // Respuestas existentes indexadas por pregunta_id
        $respuestas = Evaluacion::query()
            ->where('encuesta_id', $encuesta->id)
            ->get()
            ->keyBy('pregunta_id');

        $totalPreguntas = (int) $preguntas->count();
        $contestadas    = (int) $respuestas->count();
        $empleadoObj    = User::select('id','nombre','apellido_paterno','apellido_materno')->findOrFail($empleado);

        return view('encuestas.encuesta-show', [
            'usuario'        => $user,
            'anio'           => $anio,
            'mes'            => $mes,
            'empleadoId'     => $empleado,
            'empleado'       => $empleadoObj,
            'encuesta'       => $encuesta,
            'preguntas'      => $preguntas,
            'respuestas'     => $respuestas,
            'totalPreguntas' => $totalPreguntas,
            'contestadas'    => $contestadas,
        ]);
    }

    /**
     * POST /encuestas/{empleado}?anio&mes
     * Guarda y si completó, cierra y asigna 9-box -> Rendimiento.
     */
    public function submit(Request $request, int $empleado)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        // Seguridad
        if (strtolower($user->tipoUsuario->tipo_nombre ?? '') !== 'superusuario') {
            $esDeMiDepto = $user->empleados()->where('id', $empleado)->exists();
            abort_unless($esDeMiDepto, 403);
        }

        // Encuesta del periodo (o crear)
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

        // Validación: arreglo de respuestas indexado por pregunta_id
        $data = $request->validate([
            'respuestas'               => ['required', 'array'],
            'respuestas.*.pregunta_id' => ['required', 'integer', 'exists:preguntas,id'],
            'respuestas.*.puntaje'     => ['required', 'integer', 'min:0', 'max:5'],
            'respuestas.*.comentario'  => ['nullable', 'string'],
            'comentario_final'         => ['nullable', 'string'],
        ]);

        // Guardar/actualizar respuestas
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

        // Si completó, calculamos totales y resolvemos cuadrante
        if ($contestadas >= $totalPreg) {

            // === Totales por eje (desempeño / potencial) ===
            // Asumo que preguntas.categoria ∈ {'desempeno','potencial'}
            $totales = Evaluacion::query()
                ->join('preguntas','preguntas.id','=','evaluaciones.pregunta_id')
                ->where('evaluaciones.encuesta_id', $encuesta->id)
                ->selectRaw("
                    SUM(CASE WHEN preguntas.categoria = 'desempeno' THEN evaluaciones.puntaje ELSE 0 END) AS total_desempeno,
                    SUM(CASE WHEN preguntas.categoria = 'potencial'  THEN evaluaciones.puntaje ELSE 0 END) AS total_potencial
                ")
                ->first();

            $totalDesempeno = (int)($totales->total_desempeno ?? 0);
            $totalPotencial = (int)($totales->total_potencial ?? 0);

            // === Resolver 9-box con bordes inclusivos ===
            $regla = DB::table('reglas_ninebox')
                ->where('min_desempeno', '<=', $totalDesempeno)
                ->where('max_desempeno', '>=', $totalDesempeno)
                ->where('min_potencial', '<=', $totalPotencial)
                ->where('max_potencial', '>=', $totalPotencial)
                ->first();

            if (!$regla) {
                // No hay regla para ese par → NO insertes rendimiento. Deja en borrador y avisa.
                return redirect()
                    ->route('encuestas.show', ['empleado' => $empleado, 'anio' => $anio, 'mes' => $mes])
                    ->withErrors([
                        'ninebox' => "No existe regla para desempeño={$totalDesempeno} y potencial={$totalPotencial}. Revisa los rangos en reglas_ninebox."
                    ]);
            }

            // Cerrar encuesta + persistir totales y ninebox_id
            $encuesta->activa          = false;
            $encuesta->enviada_en      = now();
            $encuesta->ninebox_id      = (int)$regla->ninebox_id;
            $encuesta->total_desempeno = $totalDesempeno;
            $encuesta->total_potencial = $totalPotencial;

            if (Schema::hasColumn('encuestas', 'anio'))    $encuesta->anio    = $anio;
            if (Schema::hasColumn('encuestas', 'mes'))     $encuesta->mes     = $mes;
            if (Schema::hasColumn('encuestas', 'jefe_id')) $encuesta->jefe_id = $user->id;

            $encuesta->save();

            // Sincroniza Rendimiento (una fila por usuario/mes). Solo si hay ninebox_id válido.
            $periodo = Carbon::createFromDate((int)$anio, (int)$mes, 1)->startOfDay();

            DB::transaction(function () use ($empleado, $anio, $mes, $encuesta, $periodo, $data) {
                Rendimiento::where('usuario_id', $empleado)
                    ->whereYear('created_at', $anio)
                    ->whereMonth('created_at', $mes)
                    ->delete();

                $r = new Rendimiento([
                    'usuario_id' => $empleado,
                    'ninebox_id' => (int)$encuesta->ninebox_id, // garantizado no-nulo
                    'comentario' => $data['comentario_final'] ?? null,
                ]);
                $r->timestamps = false;
                $r->created_at = $periodo;
                $r->save();
            });

            return redirect()
                ->route('encuestas.empleados', ['anio' => $anio, 'mes' => $mes])
                ->with('success', '¡Encuesta enviada y cerrada! 9-Box asignado correctamente.');
        }

        // Borrador
        if ($encuesta->activa === false) {
            $encuesta->activa = true;
            $encuesta->save();
        }

        return redirect()
            ->route('encuestas.show', ['empleado' => $empleado, 'anio' => $anio, 'mes' => $mes])
            ->with('status', 'Borrador guardado. Puedes continuar después.');
    }
}