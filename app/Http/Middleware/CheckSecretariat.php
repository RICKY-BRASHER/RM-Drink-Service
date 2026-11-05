<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;

class CheckSecretariat
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Vérification connexion
        if (!Auth::check()) {
            return redirect()->route('login.pro')
                ->with('toast_error', 'Veuillez vous connecter pour accéder au secrétariat.');
        }

        $user = Auth::user();

        // 2. Vérification stricte du rôle
        if ($user->role_u !== 'employe') {
            Auth::logout();
            return redirect()->route('login.pro')
                ->with('toast_error', 'Accès refusé. Réservé au personnel de facturation.');
        }

        // 3. Vérification horaire du dépôt
        if ($user->depot) {
            $heureActuelle = now()->format('H:i:s');
            $open = (new DateTime($user->depot->open))->format('H:i:s');
            $close = (new DateTime($user->depot->close))->format('H:i:s');

            if ($heureActuelle < $open || $heureActuelle > $close) {
                Auth::logout();
                return redirect()->route('login.pro')
                    ->with('toast_error', "⏰ Accès refusé : le dépôt {$user->depot->nom_depot} est fermé (horaires {$open} - {$close}).");
            }

            if ($user->depot->etat_depot !== 'operationnel') {
                Auth::logout();
                return redirect()->route('login.pro')
                    ->with('toast_error', "⚠️ Accès refusé : le dépôt {$user->depot->nom_depot} est actuellement {$user->depot->etat_depot}.");
            }
        }

        return $next($request);
    }
}
