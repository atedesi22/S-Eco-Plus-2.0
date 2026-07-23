<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToAgency
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Les SuperAdmin, PDG, DG et DAF ont une vue globale
        if ($user->hasRole(['SuperAdmin', 'PDG', 'DG', 'DAF'])) {
            return $next($request);
        }

        // Vérifier si l'agent est bien assigné à une agence
        if (!$user->structure_id) {
            abort(403, "Accès refusé : Vous n'êtes assigné à aucune agence physique.");
        }

        return $next($request);
    }
}
