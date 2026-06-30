<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Evaluacion;
use App\Models\Rendimiento;
use App\Enums\RolUsuario;
use App\Services\EvaluacionService;

class EncuestaController extends Controller
{
    public function __construct(private EvaluacionService $evaluacionService) {}
    /** Lista empleados para el periodo seleccionado y computa estado/progreso básico. */
    public function listaEmpleados(Request $request)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        $totalPreg = (int) DB::table('preguntas')->count();

        $esSuper = $user->esSuperadmin();
        $esDueno = $user->esDueno();

        if ($esSuper || $esDueno) {
            // Admin: SOLO jefes
            $empleadosBase = User::query()
                ->whereHas('tipoUsuario', function ($query) {
                    $query->where('tipo_nombre', RolUsuario::Jefe->value);
                })
                ->where('empresa_id', $user->empresa_id)
                ->get();

        } else {
            // Jefe: solo sus empleados
            $empleadosBase = $user->empleados()->get();
        }

        $encuestasPeriodo = Encuesta::query()
            ->whereIn('usuario_id', $empleadosBase->pluck('id'))
            ->where('anio', $anio)
            ->where('mes', $mes)
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

        $esSuper = $user->esSuperadmin();
        $esDueno = $user->esDueno();

        // Solo jefes se limitan a sus empleados; superadmin y dueño ven todo
        if (!$esSuper && !$esDueno) {
            $esDeMiDepto = $user->empleados()->where('id', $empleado)->exists();
            abort_unless($esDeMiDepto, 403);
        }

        $encuesta = Encuesta::query()
            ->where('usuario_id', $empleado)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->first();

        if (!$encuesta) {
            $encuesta = Encuesta::create([
                'usuario_id'   => $empleado,
                'evaluador_id' => $user->id,
                'jefe_id'      => $user->id,
                'anio'         => $anio,
                'mes'          => $mes,
                'activa'       => true,
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

        $comentarioGeneral = $encuesta->notas_privadas;

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

    public function submit(Request $request, int $empleado)
    {
        $user = $request->user();
        $anio = (int)($request->query('anio', now()->year));
        $mes  = (int)($request->query('mes',  now()->month));

        // 1. Autorización
        if (!$user->esSuperadmin() && !$user->esDueno()) {
            abort_unless($user->empleados()->where('id', $empleado)->exists(), 403);
        }

        $encuesta = Encuesta::where('usuario_id', $empleado)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->first();

        if (!$encuesta) {
            $encuesta = Encuesta::create([
                'usuario_id'   => $empleado,
                'evaluador_id' => $user->id,
                'jefe_id'      => $user->id,
                'anio'         => $anio,
                'mes'          => $mes,
                'activa'       => true,
            ]);
        }

        // 3. Guardia: ya cerrada
        if ($encuesta->activa === false) {
            return redirect()->route('encuestas.show', [
                'empleado' => $empleado, 'anio' => $anio, 'mes' => $mes
            ])->with('alert', [
                'type' => 'info',
                'title' => 'Encuesta ya enviada',
                'text' => 'Esta evaluación ya había sido enviada. Solo puedes consultarla en modo lectura.',
            ]);
        }

        // 4. Validar
        $data = $this->validarRespuestas($request);

        // 5. Guardar notas y respuestas
        $encuesta->notas_privadas = $data['comentario_general'] ?? null;
        $encuesta->save();

        DB::transaction(fn() => $this->evaluacionService->guardarRespuestas($encuesta, $data['respuestas']));

        // 6. ¿Está completa?
        $totalPreg   = (int) DB::table('preguntas')->count();
        $contestadas = (int) Evaluacion::where('encuesta_id', $encuesta->id)->count();

        if ($contestadas >= $totalPreg) {
            try {
                $totales = $this->evaluacionService->calcularTotales($encuesta);
                $regla   = $this->evaluacionService->resolverCuadrante($totales['desempeno'], $totales['potencial']);
            } catch (\RuntimeException $e) {
                return redirect()->route('encuestas.show', [
                    'empleado' => $empleado, 'anio' => $anio, 'mes' => $mes
                ])->withErrors(['ninebox' => $e->getMessage()]);
            }

            $encuesta = $this->evaluacionService->cerrarEncuesta($encuesta, $totales['desempeno'], $totales['potencial'], $regla, $anio, $mes, $user);
            $rendimiento = $this->evaluacionService->registrarRendimiento($encuesta);

            $textoCuadrante = $encuesta->ninebox->nombre ?? "Cuadrante {$encuesta->ninebox_id}";

            return redirect()->route('encuestas.empleados', ['anio' => $anio, 'mes' => $mes])
                ->with('alert', [
                    'type'          => 'success',
                    'title'         => 'Encuesta enviada',
                    'text'          => "La evaluación se envió correctamente.<br>El colaborador fue asignado al cuadrante: <strong>{$textoCuadrante}</strong>.",
                    'quadrant_id'   => $encuesta->ninebox_id,
                    'quadrant_name' => $textoCuadrante,
                ]);
        }

        return redirect()->route('encuestas.empleados', ['anio' => $anio, 'mes' => $mes])
            ->with('alert', [
                'type'  => 'success',
                'title' => 'Borrador guardado',
                'text'  => 'Tus respuestas se guardaron correctamente. Puedes continuar la evaluación más tarde.',
            ]);
    }

    private function validarRespuestas(Request $request): array
    {
        $payload = $request->all();
        if (isset($payload['respuestas']) && is_array($payload['respuestas'])) {
            foreach ($payload['respuestas'] as $idx => $r) {
                if (array_key_exists('puntaje', $r) && $r['puntaje'] === '') {
                    $payload['respuestas'][$idx]['puntaje'] = null;
                }
            }
        }
        $request->replace($payload);

        return $request->validate([
            'respuestas'               => ['required', 'array'],
            'respuestas.*.pregunta_id' => ['required', 'integer', 'exists:preguntas,id'],
            'respuestas.*.puntaje'     => ['nullable', 'integer', 'between:1,5'],
            'respuestas.*.comentario'  => ['nullable', 'string'],
            'comentario_general'       => ['nullable', 'string', 'max:1000'],
        ]);
    }
}