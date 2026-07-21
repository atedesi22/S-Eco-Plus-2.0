<?php

namespace App\Http\Middleware;

use App\Models\CustomerTontine;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckClientDues
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole('client')) {
            $user = Auth::user();

            // Recherche d'un compte tontine emprunt actif avec un reste à payer
            $activeEmprunt = CustomerTontine::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereColumn('amount_reimbursed', '<', 'amount_to_reimburse')
                ->first();

            if ($activeEmprunt) {
                // Flash de session pour déclencher la boîte de dialogue Blade (Popup) à chaque connexion/page
                session()->flash('show_emprunt_warning', [
                    'amount_left' => $activeEmprunt->amount_to_reimburse - $activeEmprunt->amount_reimbursed,
                    'deadline' => Carbon::parse($activeEmprunt->deadline_date)->format('d/m/Y'),
                    'is_overdue' => Carbon::now()->gt(Carbon::parse($activeEmprunt->deadline_date))
                ]);
            }
        }
        return $next($request);
    }
}
