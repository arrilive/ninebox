<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmpresaRequest;
use App\Models\Empresa;
use Illuminate\Support\Str;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::withCount(['departamentos', 'usuarios'])->get();

        return view('admin.empresas.index', compact('empresas'));
    }

    public function crear()
    {
        return view('admin.empresas.crear');
    }

    public function store(StoreEmpresaRequest $request)
    {
        $data = $request->validated();

        // Generar slug único a partir del nombre
        $baseSlug = Str::slug($data['nombre']);
        $slug     = $baseSlug;
        $counter  = 2;
        while (Empresa::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        $data['slug']  = $slug;
        $data['activa'] = true;

        $empresa = Empresa::create($data);

        return redirect()
            ->route('admin.empresas.show', $empresa)
            ->with('success', 'Empresa creada con éxito.');
    }

    public function show(Empresa $empresa)
    {
        $departamentos = $empresa->departamentos()
            ->with('jefe')
            ->withCount('empleados')
            ->get();

        $usuariosPorTipo = $empresa->usuarios()
            ->with('tipoUsuario', 'departamento')
            ->get()
            ->groupBy(fn($u) => $u->tipoUsuario ? $u->tipoUsuario->tipo_nombre : 'Sin Rol');

        return view('admin.empresas.show', compact('empresa', 'departamentos', 'usuariosPorTipo'));
    }

    public function toggleActiva(Empresa $empresa)
    {
        $empresa->activa = !$empresa->activa;
        $empresa->save();

        $estado = $empresa->activa ? 'activada' : 'desactivada';

        return redirect()
            ->back()
            ->with('success', "La empresa fue {$estado} correctamente.");
    }
}
