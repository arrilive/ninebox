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

        $tipoId = $u->tipo_usuario_id;
        if (!in_array($tipoId, [2, 4])) { 
            abort(403);
        }

        return $next($request);
    }
}