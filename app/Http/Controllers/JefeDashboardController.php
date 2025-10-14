<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JefeDashboardController extends Controller
{
    public function index()
    {
        $jefe = Auth::user();

        // Usamos la relación auxiliar empleados() definida en User.
        // Si no tiene departamento_id, empleados retornará colección vacía.
        $empleados = $jefe->empleados ?? collect();

        return view('jefe.dashboard', compact('jefe', 'empleados'));
    }
}
