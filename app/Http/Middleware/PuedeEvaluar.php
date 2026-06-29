<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\RolUsuario;

class PuedeEvaluar
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $rol = $user->rol();
        if (!in_array($rol, [RolUsuario::Jefe, RolUsuario::Dueno, RolUsuario::Superadmin])) {
            abort(403);
        }

        return $next($request);
    }
}