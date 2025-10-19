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

class DashboardController extends Controller
{
    /**
     * Devuelve los empleados filtrados por departamento y tipo
     */
    private function empleadosDelDepartamento($jefe)
    {
        return User::where('departamento_id', $jefe->departamento_id)
            ->where('tipo_usuario_id', 3)
            ->where('id', '!=', $jefe->id);
    }

    public function index()
    {
        $jefe = Auth::user();
        if ($jefe->tipo_usuario_id != 2) {
            abort(403, 'Acceso no autorizado');
        }

        $empleados = $jefe->departamento_id
            ? $this->empleadosDelDepartamento($jefe)->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno'])
            : collect();

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
            'jefe', 
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
                $newRendimiento->timestamps = false; // evita sobrescribir created_at
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