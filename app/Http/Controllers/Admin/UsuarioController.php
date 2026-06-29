<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsuarioRequest;
use App\Models\Empresa;
use App\Models\TipoUsuario;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function crear(Empresa $empresa)
    {
        $departamentos = $empresa->departamentos;
        $tipos = TipoUsuario::where('tipo_nombre', '!=', 'Superadmin')->get();

        return view('admin.usuarios.crear', compact('empresa', 'departamentos', 'tipos'));
    }

    public function store(StoreUsuarioRequest $request, Empresa $empresa)
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario = new User($data);
        $usuario->empresa_id = $empresa->id;
        $usuario->save();

        return redirect()
            ->route('admin.empresas.show', $empresa)
            ->with('success', 'Usuario creado con éxito.');
    }

    public function destroy(Empresa $empresa, User $usuario)
    {
        abort_unless($usuario->empresa_id === $empresa->id, 403);

        $usuario->delete();

        return redirect()
            ->route('admin.empresas.show', $empresa)
            ->with('success', 'Usuario eliminado con éxito.');
    }
}
