<?php

namespace App\Http\Middleware;
     
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMegaAdmin
{
    /**
     * Solo el usuario megadmin (mega_admin) puede acceder.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('sams2_user');
        if (!$user || ($user['role'] ?? '') !== 'mega_admin') {
            abort(403, 'Solo Mega Admin puede acceder a esta sección.');
        }
        
        return $next($request);
    }
}
