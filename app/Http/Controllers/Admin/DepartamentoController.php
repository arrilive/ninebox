<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\TipoUsuario;
use App\Models\User;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    public function crear(Empresa $empresa)
    {
        $jefesDisponibles = $empresa->usuarios()
            ->whereHas('tipoUsuario', fn($q) => $q->where('tipo_nombre', 'Jefe'))
            ->get();

        return view('admin.departamentos.crear', compact('empresa', 'jefesDisponibles'));
    }

    public function store(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'jefe_id'  => 'nullable|exists:usuarios,id',
        ]);

        $departamento = $empresa->departamentos()->create([
            'nombre_departamento' => $request->nombre,
            'jefe_id'             => $request->jefe_id,
        ]);

        if ($request->jefe_id) {
            $jefe = User::find($request->jefe_id);
            $jefe->departamento_id = $departamento->id;
            
            $jefeTypeId = TipoUsuario::where('tipo_nombre', 'Jefe')->value('id');
            if ($jefeTypeId) {
                $jefe->tipo_usuario_id = $jefeTypeId;
            }
            $jefe->save();
        }

        return redirect()
            ->route('admin.empresas.show', $empresa)
            ->with('success', 'Departamento creado con éxito.');
    }

    public function destroy(Empresa $empresa, Departamento $departamento)
    {
        abort_unless($departamento->empresa_id === $empresa->id, 403);

        $departamento->delete();

        return redirect()
            ->route('admin.empresas.show', $empresa)
            ->with('success', 'Departamento eliminado con éxito.');
    }
}
