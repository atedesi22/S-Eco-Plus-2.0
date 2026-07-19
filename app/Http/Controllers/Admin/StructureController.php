<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    //
    // Affiche la console de gestion des entités et flux
    public function index()
    {
        // Charger les structures avec leur parent (DR) et le personnel rattaché
        $structures = Structure::with(['parent', 'users'])->latest()->get();

        // Uniquement les directions régionales pour alimenter le select de dépendance des agences
        $regionalDirections = Structure::regionalDirections()->get();

        // Récupérer les directeurs disponibles (sans structure assignée pour le moment)
        $availableRegionalDirectors = User::role('Directeur Regional')->whereNull('structure_id')->get();
        $availableAgencyDirectors = User::role('Directeur Agence')->whereNull('structure_id')->get();

        return view('admin.structures.index', compact(
            'structures',
            'regionalDirections',
            'availableRegionalDirectors',
            'availableAgencyDirectors'
        ));
    }

    // Création d'une nouvelle structure
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:structures,name',
            'type' => 'required|in:regional_direction,agency',
            'parent_id' => 'nullable|required_if:type,agency|exists:structures,id',
        ]);

        Structure::create([
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $request->type === 'agency' ? $request->parent_id : null,
        ]);

        return redirect()->back()->with('success', 'La structure physique a été configurée au sein du réseau.');
    }

    // Affectation d'un Directeur à sa structure dédiée
    public function assignDirector(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'structure_id' => 'required|exists:structures,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $structure = Structure::findOrFail($request->structure_id);

        // Sécurité métier : vérifier la concordance de rôle
        if ($structure->type === 'regional_direction' && !$user->hasRole('Directeur Regional')) {
            return redirect()->back()->with('error', 'Cet agent doit posséder le rôle Directeur Régional.');
        }
        if ($structure->type === 'agency' && !$user->hasRole('Directeur Agence')) {
            return redirect()->back()->with('error', "Cet agent doit posséder le rôle Directeur d'Agence.");
        }

        // Mettre à jour la structure de l'utilisateur
        $user->update(['structure_id' => $structure->id]);

        return redirect()->back()->with('success', "Le poste opérationnel de {$user->name} a été validé avec succès.");
    }
}
