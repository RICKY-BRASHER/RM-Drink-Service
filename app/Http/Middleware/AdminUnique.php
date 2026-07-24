<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class AdminUnique
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    { 
        if (!Auth::check()) {
            return redirect()->route('login.pro')->with('error', 'Veuillez vous connecter pour accéder au secrétariat.');
        }

        // 2. Vérification stricte du rôle (Cahier des charges)
        if (Auth::user()->role_u !== 'admin') {
            Auth::logout();
            return redirect()->route('login.pro')->with('error', 'Accès refusé. Réservé a la Direction.');
        }
        return $next($request);
    }
}
