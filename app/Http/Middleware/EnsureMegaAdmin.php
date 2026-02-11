<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMegaAdmin
{
    /**
     * Comprueba que el usuario sea Mega Admin (acceso a todo).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('sams2_user');
        if (! $user || ($user['role'] ?? null) !== 'mega_admin') {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        return $next($request);
    }
}
