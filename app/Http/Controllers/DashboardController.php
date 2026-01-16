<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\NineBox;
use App\Models\Rendimiento;
use App\Models\TipoUsuario;
use App\Models\Departamento;

class DashboardController extends Controller
{
    /**
     * Devuelve los empleados filtrados por departamento y tipo
     */
    private function empleadosDelDepartamento($usuario)
    {
        return User::where('departamento_id', $usuario->departamento_id)
            ->where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['empleado'])
            ->where('id', '!=', $usuario->id)
            ->with('departamento')
            ->get(['id', 'departamento_id', 'nombre', 'apellido_paterno', 'apellido_materno'])
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'nombre' => $emp->nombre,
                    'apellido_paterno' => $emp->apellido_paterno,
                    'apellido_materno' => $emp->apellido_materno,
                    'departamento_id' => $emp->departamento_id,
                    'departamento_nombre' => $emp->departamento->nombre_departamento ?? 'Sin departamento',
                ];
            });
    }

    public function index(Request $request)
    {
        $usuario = $request->user();

        // Evitar empleados
        if (method_exists($usuario, 'esEmpleado') && $usuario->esEmpleado()) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['correo' => 'No tienes permiso para acceder.']);
        }

        // Rangos de años (solo para admin/dueño, por defecto año único)
        $anioInicio = (int) $request->query('anio_inicio', $request->query('anio', now()->year));
        $anioFin    = (int) $request->query('anio_fin', $request->query('anio', now()->year));
        $anioActual = $anioInicio; // Para compatibilidad
        
        $mesInicio  = (int) $request->query('mes_inicio', now()->month);
        $mesFin     = (int) $request->query('mes_fin', now()->month);
        
        // Si no se especifica rango, usar mes único (compatibilidad hacia atrás)
        if (!$request->has('mes_inicio') && !$request->has('mes_fin')) {
            $mesInicio = (int) $request->query('mes', now()->month);
            $mesFin = $mesInicio;
        }

        // Empleados según rol
        $empleados = collect();

        $esSuper = method_exists($usuario, 'esSuperusuario') && $usuario->esSuperusuario();
        $esDueno = method_exists($usuario, 'esDueno') && $usuario->esDueno();
        $esJefe  = method_exists($usuario, 'esJefe') && $usuario->esJefe();

        // Filtros para admin/dueño
        $departamentoFiltro = $request->query('departamento');
        // Manejar múltiples departamentos: puede ser string, array o 'todos'
        // Laravel convierte automáticamente departamento[] en array
        $departamentosSeleccionados = [];
        if ($departamentoFiltro) {
            if (is_array($departamentoFiltro)) {
                // Filtrar valores vacíos y 'todos'
                $departamentosSeleccionados = array_values(array_filter(
                    $departamentoFiltro, 
                    fn($d) => $d !== 'todos' && $d !== null && $d !== ''
                ));
            } elseif ($departamentoFiltro !== 'todos') {
                $departamentosSeleccionados = [$departamentoFiltro];
            }
        }
        $rolFiltro = $request->query('rol'); // 'jefe', 'empleado', o null para ambos

        if ($esSuper || $esDueno) {
            $query = User::whereIn('tipo_usuario_id', [
                    TipoUsuario::TIPOS_USUARIO['jefe'],
                    TipoUsuario::TIPOS_USUARIO['empleado'],
                ])
                ->with('departamento');

            // Filtro por departamento (múltiple)
            if (!empty($departamentosSeleccionados)) {
                $query->whereIn('departamento_id', $departamentosSeleccionados);
            }

            // Filtro por rol
            if ($rolFiltro === 'jefe') {
                $query->where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['jefe']);
            } elseif ($rolFiltro === 'empleado') {
                $query->where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['empleado']);
            }

            $empleados = $query
                ->get(['id', 'departamento_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'tipo_usuario_id'])
                ->map(function ($emp) {
                    return [
                        'id'                  => $emp->id,
                        'nombre'              => $emp->nombre,
                        'apellido_paterno'    => $emp->apellido_paterno,
                        'apellido_materno'    => $emp->apellido_materno,
                        'departamento_id'     => $emp->departamento_id,
                        'departamento_nombre' => $emp->departamento->nombre_departamento ?? 'Sin departamento',
                        'tipo_usuario_id'     => $emp->tipo_usuario_id,
                    ];
                });

        } elseif ($esJefe) {
            // Jefe: sus empleados del departamento
            $empleados = $this->empleadosDelDepartamento($usuario);
        }

        $totalEmpleados = $empleados->count();

        $cuadrantes = NineBox::orderBy('posicion')->get();

        // Consulta de rendimientos con rango de años y meses
        $rendimientosQuery = Rendimiento::with(['usuario', 'nineBox', 'usuario.departamento'])
            ->whereIn('usuario_id', $empleados->pluck('id'));

        // Si hay rango de años, usar rango de fechas completo
        if ($anioInicio !== $anioFin) {
            // Rango que cruza años: usar fechas completas
            $fechaInicio = Carbon::create($anioInicio, $mesInicio, 1)->startOfMonth();
            $fechaFin = Carbon::create($anioFin, $mesFin, 1)->endOfMonth();
            
            $rendimientosQuery->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        } elseif ($mesInicio !== $mesFin) {
            // Mismo año, rango de meses
            $rendimientosQuery->whereYear('created_at', $anioInicio)
                ->whereBetween(
                    \DB::raw('MONTH(created_at)'),
                    [$mesInicio, $mesFin]
                );
        } else {
            // Un solo mes y año
            $rendimientosQuery->whereYear('created_at', $anioInicio)
                ->whereMonth('created_at', $mesInicio);
        }

        $rendimientos = $rendimientosQuery->get();

        $asignacionesActuales = $rendimientos
            ->map(function ($asig) {
                $u = $asig->usuario;
                return [
                    'usuario_id'          => $asig->usuario_id,
                    'ninebox_id'          => $asig->ninebox_id,
                    'nombre'              => $u->nombre ?? '',
                    'apellido_paterno'    => $u->apellido_paterno ?? '',
                    'apellido_materno'    => $u->apellido_materno ?? '',
                    'departamento_id'     => $u->departamento_id ?? null,
                    'departamento_nombre' => optional($u->departamento)->nombre_departamento ?? 'Sin departamento',
                ];
            })
            ->groupBy('ninebox_id');

        $empleadosEvaluados = $rendimientos->pluck('usuario_id')->unique()->count();

        // Obtener departamentos para el filtro (solo admin/dueño)
        $departamentos = collect();
        if ($esSuper || $esDueno) {
            $departamentos = Departamento::orderBy('nombre_departamento')->get(['id', 'nombre_departamento']);
        }

        return view('ninebox.dashboard', [
            'usuario'              => $usuario,
            'anioActual'           => $anioActual,
            'anioInicio'           => $anioInicio,
            'anioFin'              => $anioFin,
            'mesInicio'            => $mesInicio,
            'mesFin'               => $mesFin,
            'mesActual'            => $mesInicio, // Para compatibilidad
            'empleados'            => $empleados,
            'totalEmpleados'       => $totalEmpleados,
            'cuadrantes'           => $cuadrantes,
            'rendimientos'         => $rendimientos,
            'asignacionesActuales' => $asignacionesActuales,
            'empleadosEvaluados'   => $empleadosEvaluados,
            'esSuper'              => $esSuper,
            'departamentos'        => $departamentos,
            'departamentoFiltro'   => $departamentoFiltro,
            'departamentosSeleccionados' => $departamentosSeleccionados,
            'rolFiltro'            => $rolFiltro,
        ]);
    }

    public function filtrarRendimientosPorFecha(Request $request)
    {
        $jefe = Auth::user();

        $anio = (int) $request->input('anio');
        $mes  = (int) $request->input('mes');

        // Si es admin o dueño, ve todos; si es jefe, solo su depto
        $esSuper = method_exists($jefe, 'esSuperusuario') && $jefe->esSuperusuario();
        $esDueno = method_exists($jefe, 'esDueno') && $jefe->esDueno();

        if ($esSuper || $esDueno) {
            $empleados = User::whereIn('tipo_usuario_id', [
                    TipoUsuario::TIPOS_USUARIO['jefe'],
                    TipoUsuario::TIPOS_USUARIO['empleado'],
                ])->get(['id']);
        } else {
            // Jefe: solo su depto
            $empleados = $jefe->departamento_id
                ? $this->empleadosDelDepartamento($jefe)
                : collect();
        }

        $asignacionesPorFecha = Rendimiento::whereIn('usuario_id', $empleados->pluck('id'))
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->with(['usuario.departamento', 'nineBox'])
            ->get()
            ->map(function ($asig) {
                $u = $asig->usuario;
                return [
                    'usuario_id'          => $asig->usuario_id,
                    'ninebox_id'          => $asig->ninebox_id,
                    'nombre'              => $u->nombre ?? '',
                    'apellido_paterno'    => $u->apellido_paterno ?? '',
                    'apellido_materno'    => $u->apellido_materno ?? '',
                    'departamento_id'     => $u->departamento_id ?? null,
                    'departamento_nombre' => optional($u->departamento)->nombre_departamento ?? 'Sin departamento',
                ];
            })
            ->groupBy('ninebox_id');

        return response()->json([
            'asignacionesPorFecha' => $asignacionesPorFecha,
        ]);
    }

    public function guardarEvaluacion(Request $request)
    {
        $jefe = Auth::user();
        $anio = (int) $request->input('anio');
        $mes  = (int) $request->input('mes');

        $rendimientosAsignados = json_decode($request->input('rendimientosAsignados'));
        $fecha = Carbon::createFromDate($anio, $mes, today()->day)->startOfDay();

        if ($fecha->lt(now()->startOfMonth())) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden registrar rendimientos en meses anteriores al actual.',
            ], 422);
        }

        foreach ($rendimientosAsignados as $key => $rendimientos) {
            foreach ($rendimientos as $rendimiento) {
                Rendimiento::where('usuario_id', $rendimiento->usuario_id)
                    ->whereMonth('created_at', $mes)
                    ->whereYear('created_at', $anio)
                    ->delete();

                $newRendimiento = new Rendimiento([
                    'usuario_id' => $rendimiento->usuario_id,
                    'ninebox_id' => $rendimiento->ninebox_id,
                ]);
                $newRendimiento->timestamps = false;
                $newRendimiento->created_at = $fecha;
                $newRendimiento->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Evaluación guardada correctamente',
        ]);
    }
}