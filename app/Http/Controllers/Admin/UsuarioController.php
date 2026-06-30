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

    public function crearPorTipo(Empresa $empresa, string $tipo)
    {
        $allowed = ['dueno' => 'Dueño', 'jefe' => 'Jefe', 'empleado' => 'Empleado'];
        if (!array_key_exists($tipo, $allowed)) {
            abort(404);
        }

        $departamentos = $empresa->departamentos;
        return view("admin.usuarios.crear-{$tipo}", compact('empresa', 'departamentos'));
    }

    public function storePorTipo(Request $request, Empresa $empresa, string $tipo)
    {
        $mapa = ['dueno' => 'Dueño', 'jefe' => 'Jefe', 'empleado' => 'Empleado'];
        if (!isset($mapa[$tipo])) abort(404);

        $rules = [];
        if ($tipo === 'dueno') {
            $rules = [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'correo' => 'required|email|unique:usuarios,correo',
                'user_name' => 'required|string|unique:usuarios,user_name',
                'password' => 'required|string|min:8|confirmed',
            ];
        } elseif ($tipo === 'jefe') {
            $rules = [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'correo' => 'nullable|email|unique:usuarios,correo',
                'user_name' => 'required|string|unique:usuarios,user_name',
                'password' => 'required|string|min:8|confirmed',
                'departamento_id' => 'required|exists:departamentos,id',
            ];
        } elseif ($tipo === 'empleado') {
            $rules = [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'departamento_id' => 'required|exists:departamentos,id',
            ];
        }

        $data = $request->validate($rules);

        $tipoId = TipoUsuario::where('tipo_nombre', $mapa[$tipo])->value('id');
        if (!$tipoId) abort(500, 'Tipo de usuario no encontrado');

        $usuario = new User();
        $usuario->nombre = $data['nombre'];
        $usuario->apellido_paterno = $data['apellido_paterno'] ?? null;
        $usuario->apellido_materno = $data['apellido_materno'] ?? null;
        $usuario->correo = $data['correo'] ?? null;
        $usuario->user_name = $data['user_name'] ?? null;
        if (isset($data['password'])) $usuario->password = bcrypt($data['password']);
        $usuario->empresa_id = $empresa->id;
        $usuario->tipo_usuario_id = $tipoId;
        if (isset($data['departamento_id'])) $usuario->departamento_id = $data['departamento_id'];
        if ($tipo === 'empleado') $usuario->password = null;
        $usuario->save();

        if ($usuario->tipoUsuario->tipo_nombre === 'Jefe' && $usuario->departamento_id) {
            // Limpiar jefe_id de cualquier departamento que tuviera a este usuario antes
            \App\Models\Departamento::where('jefe_id', $usuario->id)
                ->where('id', '!=', $usuario->departamento_id)
                ->update(['jefe_id' => null]);

            // Asignar este usuario como jefe del nuevo departamento
            \App\Models\Departamento::where('id', $usuario->departamento_id)
                ->update(['jefe_id' => $usuario->id]);
        }

        return redirect()->route('admin.empresas.show', $empresa)->with('success', 'Usuario creado con éxito.');
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

    public function editar(Empresa $empresa, User $usuario)
    {
        abort_unless($usuario->empresa_id === $empresa->id, 403);

        $departamentos = $empresa->departamentos;
        $tipoNombre = $usuario->tipoUsuario?->tipo_nombre;
        $tipo = match ($tipoNombre) {
            'Dueño' => 'dueno',
            'Jefe' => 'jefe',
            'Empleado' => 'empleado',
            default => null,
        };

        abort_if($tipo === null, 404);

        return view("admin.usuarios.editar-{$tipo}", compact('empresa', 'usuario', 'departamentos'));
    }

    public function update(Request $request, Empresa $empresa, User $usuario)
    {
        abort_unless($usuario->empresa_id === $empresa->id, 403);

        $tipoNombre = $usuario->tipoUsuario?->tipo_nombre;
        $rules = [];

        if ($tipoNombre === 'Dueño') {
            $rules = [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'correo' => 'nullable|email|unique:usuarios,correo,' . $usuario->id,
                'user_name' => 'nullable|string|unique:usuarios,user_name,' . $usuario->id,
                'password' => 'nullable|string|min:8|confirmed',
            ];
        } elseif ($tipoNombre === 'Jefe') {
            $rules = [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'correo' => 'nullable|email|unique:usuarios,correo,' . $usuario->id,
                'user_name' => 'nullable|string|unique:usuarios,user_name,' . $usuario->id,
                'password' => 'nullable|string|min:8|confirmed',
                'departamento_id' => 'required|exists:departamentos,id',
            ];
        } elseif ($tipoNombre === 'Empleado') {
            $rules = [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'nullable|string|max:100',
                'correo' => 'nullable|email|unique:usuarios,correo,' . $usuario->id,
                'user_name' => 'nullable|string|unique:usuarios,user_name,' . $usuario->id,
                'password' => 'nullable|string|min:8|confirmed',
                'departamento_id' => 'required|exists:departamentos,id',
            ];
        } else {
            abort(404);
        }

        $data = $request->validate($rules);

        $usuario->nombre = $data['nombre'];
        $usuario->apellido_paterno = $data['apellido_paterno'] ?? null;
        $usuario->apellido_materno = $data['apellido_materno'] ?? null;
        $usuario->correo = $data['correo'] ?? null;
        $usuario->user_name = $data['user_name'] ?? null;
        if (isset($data['departamento_id'])) {
            $usuario->departamento_id = $data['departamento_id'];
        }
        if (!empty($data['password'])) {
            $usuario->password = bcrypt($data['password']);
        }
        $usuario->save();

        if ($usuario->tipoUsuario->tipo_nombre === 'Jefe' && $usuario->departamento_id) {
            // Limpiar jefe_id de cualquier departamento que tuviera a este usuario antes
            \App\Models\Departamento::where('jefe_id', $usuario->id)
                ->where('id', '!=', $usuario->departamento_id)
                ->update(['jefe_id' => null]);

            // Asignar este usuario como jefe del nuevo departamento
            \App\Models\Departamento::where('id', $usuario->departamento_id)
                ->update(['jefe_id' => $usuario->id]);
        }

        return redirect()
            ->route('admin.empresas.show', $empresa)
            ->with('success', 'Usuario actualizado con éxito.');
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
