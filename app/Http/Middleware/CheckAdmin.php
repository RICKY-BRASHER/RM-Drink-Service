<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Si l'utilisateur n'est pas connecté du tout
        if (!Auth::check()) {
            return redirect()->route('login.pro')->with('error', 'Veuillez vous connecter pour accéder à l\'administration.');
        }

        // 2. S'il est connecté mais n'est pas ADMIN ou EMPLOYE de type PERSONNEL, on le déconnecte et on le redirige vers la page de connexion
        if (Auth::user()->role_u !== 'admin' && Auth::user()->role_u !== 'employe' && Auth::user()->type_u !== 'personnel') {
            Auth::logout(); // On le déconnecte par sécurité
            return redirect()->route('login.pro')->with('error', 'Accès refusé. Cette zone est réservée à la direction. Vous venez detre deconnecter');
        }
        return $next($request);
    }
}
