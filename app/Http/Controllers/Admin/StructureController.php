<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StructureController extends Controller
{
    //
    // Affiche la console de gestion des entités et flux
    public function index()
    {
        // dd(User::role('Directeur Agence')->get());
        // Chargement des agences avec leur directeur et leurs zones rattachées
        $structures = Structure::with(['director', 'zones.manager', 'zones.agents'])
            ->withCount(['users'])
            ->get();


        // On charge les agences avec leurs relations (directeur et zones avec leurs effectifs)
        $agencies = Structure::with(['director', 'zones' => function ($query) {
            $query->withCount(['agents', 'clients'])->with('manager');
        }])->get();

        // Uniquement les directions régionales pour alimenter le select de dépendance des agences
        $regionalDirections = Structure::regionalDirections()->get();

        // Récupérer les directeurs disponibles (sans structure assignée pour le moment)
        // $availableRegionalDirectors = User::role('Directeur Regional')->whereNull('structure_id')->get();
        // $availableAgencyDirectors = User::role('Directeur Agence')->whereNull('structure_id')->get();


        $availableRegionalDirectors = User::role(['Directeur Regional'])
        ->where(function($query) {
            $query->whereNull('structure_id')->orWhere('structure_id', 0);
        })
        ->get();

    $availableAgencyDirectors = User::role(['Directeur Agence'])
        ->where(function($query) {
            $query->whereNull('structure_id')->orWhere('structure_id', 0);
        })
        ->get();


        return view('admin.structures.index', compact(
            'structures',
            'agencies',
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
    // public function assignDirector(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'structure_id' => 'required|exists:structures,id',
    //     ]);

    //     $user = User::findOrFail($request->user_id);
    //     $structure = Structure::findOrFail($request->structure_id);

    //     // Sécurité métier : vérifier la concordance de rôle
    //     if ($structure->type === 'regional_direction' && !$user->hasRole('Directeur Regional')) {
    //         return redirect()->back()->with('error', 'Cet agent doit posséder le rôle Directeur Régional.');
    //     }
    //     if ($structure->type === 'agency' && !$user->hasRole('Directeur Agence')) {
    //         return redirect()->back()->with('error', "Cet agent doit posséder le rôle Directeur d'Agence.");
    //     }

    //     // Mettre à jour la structure de l'utilisateur
    //     $user->update(['structure_id' => $structure->id]);

    //     return redirect()->back()->with('success', "Le poste opérationnel de {$user->name} a été validé avec succès.");
    // }

    public function assignDirector(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'structure_id' => 'required|exists:structures,id',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::findOrFail($validated['user_id']);
            $structure = Structure::findOrFail($validated['structure_id']);

            // 1. Assigner la structure au profil de l'utilisateur
            $user->update([
                'structure_id' => $structure->id,
            ]);

            // 2. Assigner l'utilisateur comme directeur de la structure
            $structure->update([
                'director_id' => $user->id,
            ]);
        });

        return redirect()->back()->with('success', 'Le décret d\'affectation a été signé avec succès.');
    }
}
