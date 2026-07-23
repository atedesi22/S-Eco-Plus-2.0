<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Si c'est un client, on l'envoie vers son espace
        if ($user->hasRole('Client')) {
            return redirect()->route('client.dashboard');
        }

        // Si c'est un comptable
        if ($user->hasRole('Comptable')) {
            return redirect()->route('comptabilite.dashboard');
        }

        // Si c'est une secrétaire
        if ($user->hasRole('Secretaire')) {
            return redirect()->route('secretaire.dashboard');
        }

        // Pour la direction et les admins
        if ($user->hasAnyRole(['SuperAdmin', 'PDG', 'DG'])) {
            return redirect()->route('admin.dashboard');
        }

        // Sécurité par défaut pour le personnel générique
        return view('dashboard');
    }
}
