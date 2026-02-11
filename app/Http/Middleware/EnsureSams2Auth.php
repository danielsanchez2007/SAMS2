<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Usuario;

class EnsureSams2Auth
{
    /**
     * Comprueba que el usuario tenga sesión SAMS2 (login).
     * También verifica que el usuario tenga foto y firma cargadas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('sams2_user')) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
        }

        $userSession = session('sams2_user');
        
        // El mega admin no necesita foto ni firma
        if (($userSession['id'] ?? null) === 'mega_admin') {
            return $next($request);
        }

        // Verificar que el usuario tenga foto y firma
        $usuario = Usuario::find($userSession['id'] ?? null);
        
        if ($usuario) {
            // Excluir la ruta de perfil para que puedan actualizar su información
            $rutaActual = $request->route()->getName();
            
            if ($rutaActual !== 'perfil' && $rutaActual !== 'perfil.update') {
                if (empty($usuario->imagen_usuario) || empty($usuario->firma_imagen)) {
                    return redirect()->route('perfil')
                        ->with('warning', 'Debes cargar tu foto y firma antes de poder usar el sistema. Por favor, completa tu perfil.');
                }
            }
        }

        return $next($request);
    }
}
