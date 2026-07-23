<?php

namespace App\Http\Controllers\Secretaire;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecretaireDashboardController extends Controller
{
    /**
     * Dashboard Secrétariat de l'Agence
     */
    public function index()
    {
        $secretaire = Auth::user();
        $agency = Structure::findOrFail($secretaire->structure_id);

        // Nouveaux clients inscrits dans cette agence ce mois-ci
        $clientsDuMois = User::role('Client')
            ->where('structure_id', $agency->id)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Zones rattachées à l'agence pour l'attribution des nouveaux clients
        $zones = Zone::where('structure_id', $agency->id)->get();

        return view('secretaire.dashboard', compact('agency', 'clientsDuMois', 'zones'));
    }

    /**
     * Inscription / Ouverture de Compte rapide par la Secrétaire
     */
    public function storeClient(Request $request)
    {
        $secretaire = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'zone_id' => 'required|exists:zones,id',
        ]);

        // Création du client forcé sur la structure de la secrétaire
        $client = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => bcrypt('12345678'), // Mot de passe par défaut
            'structure_id' => $secretaire->structure_id,
            'zone_id' => $validated['zone_id'],
        ]);

        $client->assignRole('Client');

        return redirect()->back()->with('success', 'Compte client ouvert avec succès.');
    }
}
