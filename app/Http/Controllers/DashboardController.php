<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\NineBox;
use App\Models\Rendimiento;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Enums\RolUsuario;

class DashboardController extends Controller
{
    /**
     * Devuelve los empleados filtrados por departamento y tipo
     */
    private function empleadosDelDepartamento($usuario)
    {
        return User::where('departamento_id', $usuario->departamento_id)
            ->whereHas('tipoUsuario', fn($q) => $q->where('tipo_nombre', RolUsuario::Empleado->value))
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
        if ($usuario->esEmpleado()) {
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

        $esSuper = $usuario->esSuperadmin();
        $esDueno = $usuario->esDueno();
        $esJefe  = $usuario->esJefe();

        // Filtro por empresa
        if ($esSuper) {
            $empresaFiltroId = (int) $request->query('empresa_id', 0);
            $empresas = Empresa::where('activa', true)->orderBy('nombre')->get();
        } else {
            $empresaFiltroId = (int) $usuario->empresa_id;
            $empresas = collect();
        }

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
            $query = User::whereHas('tipoUsuario', fn($q) => $q->whereIn('tipo_nombre', [
                    RolUsuario::Jefe->value,
                    RolUsuario::Empleado->value,
                ]))
                ->with('departamento');

            if ($empresaFiltroId > 0) {
                $query->where('empresa_id', $empresaFiltroId);
            }

            // Filtro por departamento (múltiple)
            if (!empty($departamentosSeleccionados)) {
                $query->whereIn('departamento_id', $departamentosSeleccionados);
            }

            // Filtro por rol
            if ($rolFiltro === 'jefe') {
                $query->whereHas('tipoUsuario', fn($q) => $q->where('tipo_nombre', RolUsuario::Jefe->value));
            } elseif ($rolFiltro === 'empleado') {
                $query->whereHas('tipoUsuario', fn($q) => $q->where('tipo_nombre', RolUsuario::Empleado->value));
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

        // Filtrar rendimientos por columnas `anio` y `mes` (no por created_at)
        if ($anioInicio !== $anioFin) {
            $rendimientosQuery->where(fn($q) => $q
                    ->where('anio', '>', $anioInicio)
                    ->orWhere(fn($q2) => $q2->where('anio', $anioInicio)->where('mes', '>=', $mesInicio))
                )
                ->where(fn($q) => $q
                    ->where('anio', '<', $anioFin)
                    ->orWhere(fn($q2) => $q2->where('anio', $anioFin)->where('mes', '<=', $mesFin))
                );
        } elseif ($mesInicio !== $mesFin) {
            $rendimientosQuery->where('anio', $anioInicio)
                ->whereBetween('mes', [$mesInicio, $mesFin]);
        } else {
            $rendimientosQuery->where('anio', $anioInicio)
                ->where('mes', $mesInicio);
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
            $departamentos = Departamento::orderBy('nombre_departamento')
                ->when($empresaFiltroId > 0, fn($q) => $q->where('empresa_id', $empresaFiltroId))
                ->get(['id', 'nombre_departamento']);
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
            'empresas'             => $empresas,
            'empresaFiltroId'      => $empresaFiltroId,
        ]);
    }


}