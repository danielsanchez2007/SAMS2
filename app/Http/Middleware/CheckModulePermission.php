<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\PermisoHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /**
     * Handle an incoming request.
     * 
     * Verifica si el usuario tiene permiso de acceso al módulo.
     */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        if (!PermisoHelper::puede($modulo, 'acceso')) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
