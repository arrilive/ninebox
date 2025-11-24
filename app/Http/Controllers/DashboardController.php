<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $usuario = Auth::user();

        // Bloquea a empleados
        if (method_exists($usuario, 'esEmpleado') && $usuario->esEmpleado()) {
            Auth::guard('web')->logout();
            return redirect()->route('login')
                ->with('error', 'Sesión cerrada. No tienes acceso a esta sección.');
        }

        $anioActual = (int) $request->query('anio', now()->year);
        $mesActual  = (int) $request->query('mes',  now()->month);

        // Empleados según rol
        $empleados = collect();

        if (method_exists($usuario, 'esSuperusuario') && $usuario->esSuperusuario()) {
            $empleados = User::where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['empleado'])
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
        } elseif (method_exists($usuario, 'esJefe') && $usuario->esJefe()) {
            $empleados = $this->empleadosDelDepartamento($usuario);
        }

        $totalEmpleados = $empleados->count();

        $cuadrantes = NineBox::orderBy('posicion')->get();

        $rendimientos = Rendimiento::with(['usuario', 'nineBox', 'usuario.departamento'])
            ->whereIn('usuario_id', $empleados->pluck('id'))
            ->whereYear('created_at', $anioActual)
            ->whereMonth('created_at', $mesActual)
            ->get();

        // Estructura base por cuadrante
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

        // KPI evaluados (usuarios únicos con rendimiento en el periodo)
        $empleadosEvaluados = $rendimientos->pluck('usuario_id')->unique()->count();

        // Bandera para el Blade (render especial del modal)
        $esSuper = method_exists($usuario, 'esSuperusuario') && $usuario->esSuperusuario();

        return view('ninebox.dashboard', [
            'usuario'               => $usuario,
            'anioActual'            => $anioActual,
            'mesActual'             => $mesActual,
            'empleados'             => $empleados,
            'totalEmpleados'        => $totalEmpleados,
            'cuadrantes'            => $cuadrantes,
            'rendimientos'          => $rendimientos,
            'asignacionesActuales'  => $asignacionesActuales,
            'empleadosEvaluados'    => $empleadosEvaluados,
            'esSuper'               => $esSuper, // ← clave para modal solo-lectura agrupado
        ]);
    }

    public function filtrarRendimientosPorFecha(Request $request)
    {
        $jefe = Auth::user();

        $anio = (int) $request->input('anio');
        $mes  = (int) $request->input('mes');

        // Si es superusuario, ve todos; si es jefe, solo su depto
        if (method_exists($jefe, 'esSuperusuario') && $jefe->esSuperusuario()) {
            $empleados = User::where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['empleado'])->get(['id']);
        } else {
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