<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    //
    protected function authenticated($request, $user)
{
    // Si l'utilisateur possède le rôle Client, il va sur son espace dédié
    if ($user->hasRole('Client')) {
        return redirect('/client/dashboard');
    }

    // Sinon (équipes internes de la microfinance), il va sur la console générale
    return redirect('/dashboard');
}
}
