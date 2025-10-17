<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\NineBox;
use App\Models\Rendimiento;
use Illuminate\Validation\Rule;

class JefeDashboardController extends Controller
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

        $empleados = $jefe->departamento_id
            ? $this->empleadosDelDepartamento($jefe)->get()
            : collect();

        $cuadrantes = NineBox::orderBy('posicion')->get();

        $asignacionesActuales = Rendimiento::whereIn('usuario_id', $empleados->pluck('id'))
            ->whereDate('fecha', today())
            ->with('usuario', 'nineBox')
            ->get()
            ->groupBy('ninebox_id')
            ->map(fn($grupo) => $grupo->count())
            ->toArray();
            
        $empleadosEvaluados = Rendimiento::whereIn('usuario_id', $empleados->pluck('id'))
            ->whereDate('fecha', today())
            ->distinct('usuario_id')
            ->count('usuario_id');

        return view('jefe.dashboard', compact(
            'jefe', 
            'empleados', 
            'cuadrantes',
            'asignacionesActuales',
            'empleadosEvaluados'
        ));
    }

    public function asignarEmpleado(Request $request)
    {
        $payload = $request->validate([
            'usuario_id' => ['required', 'integer', Rule::exists('usuarios', 'id')],
            'ninebox_id' => ['required', 'integer', Rule::exists('nine_box', 'id')],
        ]);

        $jefe = Auth::user();
        $empleado = $this->empleadosDelDepartamento($jefe)->findOrFail($payload['usuario_id']);

        DB::transaction(function() use ($payload) {
            Rendimiento::where('usuario_id', $payload['usuario_id'])
                ->whereDate('fecha', today())
                ->delete();

            Rendimiento::create([
                'usuario_id' => $payload['usuario_id'],
                'ninebox_id' => $payload['ninebox_id'],
                'fecha' => today(),
                'comentario' => null
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Empleado asignado correctamente']);
    }

    public function eliminarAsignacion(Request $request)
    {
        $payload = $request->validate([
            'usuario_id' => ['required', 'integer', Rule::exists('usuarios', 'id')],
        ]);

        $jefe = Auth::user();
        $empleado = $this->empleadosDelDepartamento($jefe)->findOrFail($payload['usuario_id']);

        Rendimiento::where('usuario_id', $payload['usuario_id'])
            ->whereDate('fecha', today())
            ->delete();

        return response()->json(['success' => true, 'message' => 'Asignación eliminada']);
    }

    public function obtenerEmpleadosCuadrante($nineboxId)
    {
        $jefe = Auth::user();

        $todosEmpleados = $this->empleadosDelDepartamento($jefe)->get();

        $asignados = Rendimiento::where('ninebox_id', $nineboxId)
            ->whereDate('fecha', today())
            ->whereIn('usuario_id', $todosEmpleados->pluck('id'))
            ->with('usuario')
            ->get()
            ->pluck('usuario')
            ->values();

        $idsAsignadosHoy = Rendimiento::whereIn('usuario_id', $todosEmpleados->pluck('id'))
            ->whereDate('fecha', today())
            ->pluck('usuario_id')
            ->unique()
            ->values();

        $disponibles = $todosEmpleados->whereNotIn('id', $idsAsignadosHoy)->values();

        $asignadosJson = $asignados->map(fn($u) => [
            'id' => $u->id,
            'nombre' => $u->nombre,
            'apellido_paterno' => $u->apellido_paterno,
            'apellido_materno' => $u->apellido_materno,
        ]);

        $disponiblesJson = $disponibles->map(fn($u) => [
            'id' => $u->id,
            'nombre' => $u->nombre,
            'apellido_paterno' => $u->apellido_paterno,
            'apellido_materno' => $u->apellido_materno,
        ]);

        return response()->json([
            'asignados' => $asignadosJson,
            'disponibles' => $disponiblesJson,
        ]);
    }

    public function guardarEvaluacion(Request $request)
    {
        $jefe = Auth::user();
        
        $empleados = $this->empleadosDelDepartamento($jefe)->get();

        $evaluados = Rendimiento::whereIn('usuario_id', $empleados->pluck('id'))
            ->whereDate('fecha', today())
            ->distinct('usuario_id')
            ->count('usuario_id');

        if ($evaluados < $empleados->count()) {
            return response()->json([
                'error' => 'Debes evaluar a todos los empleados antes de guardar'
            ], 422);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Evaluación guardada correctamente',
            'fecha' => today()->format('d/m/Y')
        ]);
    }
}