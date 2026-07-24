<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class checklogin
{
    /**
     * Gère la redirection automatique des utilisateurs déjà connectés.
     */
    public function handle(Request $request, Closure $next)
    {
        // On vérifie si l'utilisateur possède une session active
        if (Auth::check()) {
            $user = Auth::user();

            // Scénario A : C'est un Administrateur -> Direction Console Maîtresse
            if ($user->role_u === 'admin') {
                return redirect()->route('accueil.admin');
            }
            
            // Scénario B : C'est une Secrétaire / Personnel -> Direction Caisse dépôt
            if ($user->role_u === 'employe' && $user->type_u === 'personnel') {
                return redirect()->route('dashboard');
            }
            
            // Scénario C : C'est un Client -> Direction son Espace Client
            if ($user->role_u === 'client') {
                return redirect()->route('client.accueil');
            }
        }

        // Si l'utilisateur n'est pas connecté, on le laisse passer normalement
        return $next($request);
    }
}
