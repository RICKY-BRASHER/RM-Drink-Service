<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si la session 'U_id' n'existe pas, redirection vers login
        if (!session()->has('U_id')) { 
            return redirect()->route('connexion')->with('error', 'Veuillez vous connecter d\'abord.');
        }
        return $next($request);
    }
}
