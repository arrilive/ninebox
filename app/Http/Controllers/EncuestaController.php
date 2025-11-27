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
use App\Models\TipoUsuario;

class EncuestaController extends Controller
{
    /** Lista empleados para el periodo seleccionado y computa estado/progreso básico. */
    public function listaEmpleados(Request $request)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        $totalPreg = (int) DB::table('preguntas')->count();

        $esSuper = method_exists($user, 'esSuperusuario') && $user->esSuperusuario();
        $esDueno = method_exists($user, 'esDueno') && $user->esDueno();

        if ($esSuper || $esDueno) {
            // Admin: SOLO jefes
            $empleadosBase = User::query()
                ->where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['jefe'])
                ->get();

        } else {
            // Jefe: solo sus empleados
            $empleadosBase = $user->empleados()->get();
        }

        $encuestasPeriodo = Encuesta::query()
            ->whereIn('usuario_id', $empleadosBase->pluck('id'))
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->get()
            ->keyBy('usuario_id');

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

    /** Muestra la encuesta; crea borrador si no existe. */
    public function show(Request $request, int $empleado)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        $esSuper = method_exists($user, 'esSuperusuario') && $user->esSuperusuario();
        $esDueno = method_exists($user, 'esDueno') && $user->esDueno();

        // Solo jefes se limitan a sus empleados; superadmin y dueño ven todo
        if (!$esSuper && !$esDueno) {
            $esDeMiDepto = $user->empleados()->where('id', $empleado)->exists();
            abort_unless($esDeMiDepto, 403);
        }

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

        $preguntas = Pregunta::orderBy('categoria')->orderBy('id')->get();

        $respuestas = Evaluacion::query()
            ->where('encuesta_id', $encuesta->id)
            ->get()
            ->keyBy('pregunta_id');

        $totalPreguntas    = (int) $preguntas->count();
        $contestadas       = (int) $respuestas->count();

        $empleadoObj       = User::select('id','nombre','apellido_paterno','apellido_materno')->findOrFail($empleado);

        $comentarioGeneral = null;
        if (Schema::hasColumn('encuestas', 'notas_privadas')) {
            $comentarioGeneral = $encuesta->notas_privadas;
        }

        $soloLectura = ($encuesta->activa === false) || session('ya_enviada', false);

        return view('encuestas.encuesta-show', [
            'usuario'           => $user,
            'anio'              => $anio,
            'mes'               => $mes,
            'empleadoId'        => $empleado,
            'empleado'          => $empleadoObj,
            'encuesta'          => $encuesta,
            'preguntas'         => $preguntas,
            'respuestas'        => $respuestas,
            'totalPreguntas'    => $totalPreguntas,
            'contestadas'       => $contestadas,
            'comentarioGeneral' => $comentarioGeneral,
            'soloLectura'       => $soloLectura,
        ]);
    }

    /**
     * Guarda borrador o envía; al completar cierra y asigna ninebox.
     * El comentario final se almacena en encuestas.notas_privadas.
     */
    public function submit(Request $request, int $empleado)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        $esSuper = method_exists($user, 'esSuperusuario') && $user->esSuperusuario();
        $esDueno = method_exists($user, 'esDueno') && $user->esDueno();

        // Solo jefes se limitan a sus empleados; superadmin y dueño ven todo
        if (!$esSuper && !$esDueno) {
            $esDeMiDepto = $user->empleados()->where('id', $empleado)->exists();
            abort_unless($esDeMiDepto, 403);
        }

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

        if ($encuesta->activa === false) {
            // Ya está cerrada: vuelve a la vista (modo lectura)
            return redirect()
                ->route('encuestas.show', ['empleado' => $empleado, 'anio' => $anio, 'mes' => $mes])
                ->with('alert', [
                    'type'  => 'info',
                    'title' => 'Encuesta ya enviada',
                    'text'  => 'Esta evaluación ya había sido enviada. Solo puedes consultarla en modo lectura.',
                ]);
        }

        // Normalizar payload: "" -> null en puntajes
        $payload = $request->all();
        if (isset($payload['respuestas']) && is_array($payload['respuestas'])) {
            foreach ($payload['respuestas'] as $idx => $r) {
                if (array_key_exists('puntaje', $r) && $r['puntaje'] === '') {
                    $payload['respuestas'][$idx]['puntaje'] = null;
                }
            }
        }
        $request->replace($payload);

        $data = $request->validate([
            'respuestas'               => ['required', 'array'],
            'respuestas.*.pregunta_id' => ['required', 'integer', 'exists:preguntas,id'],
            'respuestas.*.puntaje'     => ['nullable', 'integer', 'between:1,5'],
            'respuestas.*.comentario'  => ['nullable', 'string'],
            'comentario_general'       => ['nullable', 'string', 'max:1000'],
        ]);

        // Guardar comentario general (si existe la columna)
        if (Schema::hasColumn('encuestas', 'notas_privadas')) {
            $encuesta->notas_privadas = $data['comentario_general'] ?? null;
            $encuesta->save();
        }

        // Guardar/actualizar respuestas de forma segura (sin PK compuesta en Eloquent)
        DB::transaction(function () use ($encuesta, $data) {
            foreach ($data['respuestas'] as $r) {
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
        });

        $totalPreg   = (int) DB::table('preguntas')->count();
        $contestadas = (int) Evaluacion::where('encuesta_id', $encuesta->id)->count();

        // Si está completa => cerrar y ENVIAR
        if ($contestadas >= $totalPreg) {
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

            $regla = DB::table('reglas_ninebox')
                ->where('min_desempeno', '<=', $totalDesempeno)
                ->where('max_desempeno', '>=', $totalDesempeno)
                ->where('min_potencial', '<=', $totalPotencial)
                ->where('max_potencial', '>=', $totalPotencial)
                ->first();

            if (!$regla) {
                return redirect()
                    ->route('encuestas.show', ['empleado' => $empleado, 'anio' => $anio, 'mes' => $mes])
                    ->withErrors([
                        'ninebox' => "No existe regla para desempeño={$totalDesempeno} y potencial={$totalPotencial}. Revisa reglas_ninebox."
                    ]);
            }

            // Cerrar encuesta y escribir métricas
            $encuesta->activa          = false;
            $encuesta->enviada_en      = now();
            $encuesta->ninebox_id      = (int)$regla->ninebox_id;
            $encuesta->total_desempeno = $totalDesempeno;
            $encuesta->total_potencial = $totalPotencial;

            if (Schema::hasColumn('encuestas', 'anio'))    $encuesta->anio    = $anio;
            if (Schema::hasColumn('encuestas', 'mes'))     $encuesta->mes     = $mes;
            if (Schema::hasColumn('encuestas', 'jefe_id')) $encuesta->jefe_id = $user->id;

            $encuesta->save();

            $periodo = Carbon::createFromDate((int)$anio, (int)$mes, 1)->startOfDay();

            DB::transaction(function () use ($empleado, $anio, $mes, $encuesta, $periodo) {
                Rendimiento::where('usuario_id', $empleado)
                    ->whereYear('created_at', $anio)
                    ->whereMonth('created_at', $mes)
                    ->delete();

                $r = new Rendimiento([
                    'usuario_id' => $empleado,
                    'ninebox_id' => (int)$encuesta->ninebox_id,
                ]);
                $r->timestamps = false;
                $r->created_at = $periodo;
                $r->save();
            });

            // Texto de cuadrante para la alerta
            $cuadranteId = (int) $encuesta->ninebox_id;

            $nombresCuadrantes = [
                6 => 'Diamante en bruto',
                8 => 'Estrella en desarrollo',
                9 => 'Estrella',
                2 => 'Mal empleado',
                5 => 'Personal sólido',
                7 => 'Elemento importante',
                1 => 'Inaceptable',
                3 => 'Aceptable',
                4 => 'Personal clave',
            ];

            $textoCuadrante = $nombresCuadrantes[$cuadranteId] ?? "Cuadrante {$cuadranteId}";

            return redirect()
                ->route('encuestas.empleados', ['anio' => $anio, 'mes' => $mes])
                ->with('alert', [
                    'type'          => 'success',
                    'title'         => 'Encuesta enviada',
                    'text'          => "La evaluación se envió correctamente.<br>El colaborador fue asignado al cuadrante: <strong>{$textoCuadrante}</strong>.",
                    'quadrant_id'   => $cuadranteId,
                    'quadrant_name' => $textoCuadrante,
                ]);

        }

        if ($encuesta->activa === false) {
            $encuesta->activa = true;
            $encuesta->save();
        }

        return redirect()
            ->route('encuestas.empleados', ['anio' => $anio, 'mes' => $mes])
            ->with('alert', [
                'type'  => 'success',
                'title' => 'Borrador guardado',
                'text'  => 'Tus respuestas se guardaron correctamente. Puedes continuar la evaluación más tarde.',
            ]);
    }
}