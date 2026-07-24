<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSecretariat
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Si l'employé n'est pas connecté
        if (!Auth::check()) {
            return redirect()->route('login.pro')->with('error', 'Veuillez vous connecter pour accéder au secrétariat.');
        }

        // 2. Vérification stricte du rôle (Cahier des charges)
        if (Auth::user()->role_u !== 'employe') {
            Auth::logout();
            return redirect()->route('login.pro')->with('error', 'Accès refusé. Réservé au personnel de facturation.');
        }

        return $next($request);
    }
}
