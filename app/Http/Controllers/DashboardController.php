<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\NineBox;
use App\Models\Rendimiento;
use Illuminate\Validation\Rule;
use App\Models\TipoUsuario;

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
            ->map(function($emp) {
            return [
                'id' => $emp->id,
                'nombre' => $emp->nombre,
                'apellido_paterno' => $emp->apellido_paterno,
                'apellido_materno' => $emp->apellido_materno,
                'departamento_id' => $emp->departamento_id,
                'departamento_nombre' => $emp->departamento->nombre ?? 'Sin departamento'
            ];
         });
    }

   public function index()
    {
       $usuario = Auth::user();

        if ($usuario->esEmpleado()) {
            // Cierra la sesión manualmente
            Auth::guard('web')->logout();
            return redirect()->route('login')
                ->with('error', 'Sesión cerrada. No tienes acceso a esta sección.');
        }
    
        $empleados = collect();

        if($usuario->esSuperusuario()){
            $empleados = User::where('tipo_usuario_id', TipoUsuario::TIPOS_USUARIO['empleado'])
                ->with('departamento')
                ->get(['id', 'departamento_id', 'nombre', 'apellido_paterno', 'apellido_materno'])
                ->map(function($emp) {
                    return [
                        'id' => $emp->id,
                        'nombre' => $emp->nombre,
                        'apellido_paterno' => $emp->apellido_paterno,
                        'apellido_materno' => $emp->apellido_materno,
                        'departamento_id' => $emp->departamento_id,
                        'departamento_nombre' => $emp->departamento->nombre ?? 'Sin departamento'
                    ];
                });
        }
        
        if($usuario->esJefe()){
            $empleados = $this->empleadosDelDepartamento($usuario);
        }

        $cuadrantes = NineBox::orderBy('posicion')->get();

        $asignacionesActuales = Rendimiento::whereIn('usuario_id', $empleados->pluck('id'))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with('usuario', 'nineBox')
            ->get(['usuario_id', 'ninebox_id'])
            ->groupBy('ninebox_id');

        $empleadosEvaluados = $asignacionesActuales->flatten(1)
            ->pluck('usuario_id')
            ->unique()
            ->values()
            ->count();

        return view('ninebox.dashboard', compact(
            'usuario',
            'empleados', 
            'cuadrantes',
            'asignacionesActuales',
            'empleadosEvaluados'
        ));
    }

    public function filtrarRendimientosPorFecha(Request $request)
    {
        $jefe = Auth::user();

        $anio = $request->input('anio');
        $mes = $request->input('mes');

        $empleados = $jefe->departamento_id
            ? $this->empleadosDelDepartamento($jefe)->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno'])
            : collect();

        $asignacionesPorFecha = Rendimiento::whereIn('usuario_id', $empleados->pluck('id'))
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->with('usuario', 'nineBox')
            ->get(['usuario_id', 'ninebox_id'])
            ->groupBy('ninebox_id');

        return response()->json([
            'asignacionesPorFecha' => $asignacionesPorFecha,
        ]);
    }

    public function guardarEvaluacion(Request $request)
    {
        $jefe = Auth::user();
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        
        $rendimientosAsignados = json_decode($request->input('rendimientosAsignados'));
        $fecha = Carbon::createFromDate((int)$anio, (int)$mes, today()->day)->startOfDay();
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