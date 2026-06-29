<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TipoUsuario;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoUsuarioId = $this->input('tipo_usuario_id');
        $tipoUsuario = TipoUsuario::find($tipoUsuarioId);
        $isEmpleado = $tipoUsuario && $tipoUsuario->tipo_nombre === 'Empleado';

        return [
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'correo'           => 'nullable|email|unique:usuarios,correo',
            'user_name'        => 'nullable|string|max:60|unique:usuarios,user_name',
            'password'         => $isEmpleado ? 'nullable' : 'nullable|string|min:8|confirmed',
            'tipo_usuario_id'  => 'required|exists:tipos_usuarios,id',
            'departamento_id'  => 'nullable|exists:departamentos,id',
        ];
    }
}
