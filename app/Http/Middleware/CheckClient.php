<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use symfony\Component\HttpFoundation\Response;

class CheckClient
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Si le client n'est pas connecté
        if (!Auth::check()) {
            return redirect()->route('login.client')->with('error', 'Veuillez vous connecter pour accéder à votre espace.');
        }

        // 2. Si un admin ou un employé tente d'entrer ici
        if (Auth::user()->role_u !== 'client') {
            // On ne le déconnecte pas, on le redirige juste vers son propre espace légitime
            if (Auth::user()->role_u === 'admin') {
                return redirect()->route('accueil.admin');
            }
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
