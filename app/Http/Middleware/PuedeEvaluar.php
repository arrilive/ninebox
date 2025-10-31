<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PuedeEvaluar
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u) {
            abort(403);
        }

        // Ajusta a cómo guardas el rol en tu tabla
        $rol = strtolower($u->tipoUsuario->tipo_nombre ?? '');

        // Permitir sólo Jefe y Superusuario
        if (!in_array($rol, ['jefe', 'superusuario'])) {
            abort(403);
        }

        return $next($request);
    }
}