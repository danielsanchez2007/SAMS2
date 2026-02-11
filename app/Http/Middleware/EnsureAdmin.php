<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;

class EnsureAdmin
{
    /**
     * Verifica que el usuario sea mega_admin o tenga el rol "Administrador".
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('sams2_user');
        
        $esAdmin = false;
        
        // Verificar si es mega_admin
        if (($user['role'] ?? null) === 'mega_admin') {
            $esAdmin = true;
        } elseif (isset($user['role_id'])) {
            // Verificar si tiene el rol "Administrador"
            $role = Role::find($user['role_id']);
            if ($role && strtolower($role->nombre) === 'administrador') {
                $esAdmin = true;
            }
        }
        
        if (!$esAdmin) {
            abort(403, 'Solo los administradores pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
