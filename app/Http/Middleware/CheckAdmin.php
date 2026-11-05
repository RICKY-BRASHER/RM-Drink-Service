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

        $user = Auth::user();
        $isAdministrator = $user->role_u === 'admin';
        $isPersonnel = $user->role_u === 'employe' && $user->type_u === 'personnel';

        if (!$isAdministrator && !$isPersonnel) {
            abort(403, 'Accès réservé à la direction et au personnel autorisé.');
        }

        return $next($request);
    }
}
