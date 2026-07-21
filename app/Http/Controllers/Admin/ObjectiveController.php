<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerTontine;
use App\Models\Objective;
use App\Models\Sanction;
use App\Models\Tontine_plan;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class ObjectiveController extends Controller
{
    //
    public function index(Request $request)
    {
        // Récupération des objectifs avec filtres de statut facultatifs
        $status = $request->input('status');

        $query = Objective::with(['user', 'sanctions'])->orderBy('end_date', 'asc');

        if ($status) {
            $query->where('status', $status);
        }

        // Tous les rôles internes applicables
        $roles = Role::where('name', '!=', 'client')->pluck('name');

        $objectives = $query->paginate(10);
        // On récupère uniquement le personnel interne (exclut les clients)
        $staffMembers = User::withoutRole('client')->get();

        return view('admin.objectives.index', compact('objectives', 'roles', 'staffMembers', 'status'));
    }

    /**
     * Création d'un objectif pour le personnel
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:new_tontines,product_sales,collecte_amount',
            'target_value' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'role_name' => 'nullable|string',
        ]);

        // Vérification : Soit un membre précis, soit un rôle groupé
        if (!$request->filled('user_id') && !$request->filled('role_name')) {
            return back()->withErrors(['target' => 'Veuillez sélectionner soit un collaborateur, soit un rôle groupé.']);
        }

        Objective::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'target_value' => $validated['target_value'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'user_id' => $request->user_id,
            'role_name' => $request->role_name,
            'status' => 'in_progress',
        ]);

        return redirect()->back()->with('success', 'Objectif du personnel enregistré avec succès.');
    }

    public function storeSanction(Request $request, $id)
    {
        $objective = Objective::findOrFail($id);
        $user = $objective->user; // L'utilisateur visé par l'objectif raté

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'severity' => 'required|in:warning,suspension,financial_penalty,dismissal',
            'financial_penalty_amount' => 'nullable|numeric|min:0',
        ]);

        Sanction::create([
            'user_id' => $objective->user_id,
            'objective_id' => $objective->id,
            'reason' => $validated['reason'],
            'severity' => $validated['severity'],
            'financial_penalty_amount' => $validated['financial_penalty_amount'] ?? 0,
            'applied_at' => now(),
            'is_active' => true,
        ]);

        if (in_array($validated['severity'], ['suspension', 'dismissal'])) {
        $objective->update(['status' => 'failed']);
        }

        // 2. LOGIQUE CAS SPÉCIFIQUE : CLIENT
        if ($user->hasRole('client')) {
            // Récupération du plan global "Tontine Emprunt"
            $empruntPlan = Tontine_plan::where('slug', 'tontine-emprunt')->first();

            if ($empruntPlan) {
                // Création/Liaison automatique de la Tontine Emprunt pour ce client spécifique
                CustomerTontine::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tontine_plan_id' => $empruntPlan->id,
                    ],
                    [
                        'amount_to_reimburse' => $validated['financial_penalty_amount'],
                        'amount_reimbursed' => 0,
                        'deadline_date' => $objective->end_date, // La date limite correspond à l'échéance ratée
                        'is_active' => true
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Sanction enregistrée et appliquée au collaborateur.');
    }

    /**
     * Afficher le profil complet et les performances d'un collaborateur.
     */
    public function userProfile($id)
    {
        // Charge l'utilisateur avec ses objectifs et toutes ses sanctions reçues
        $user = User::with(['objectives', 'sanctions' => function($query) {
            $query->orderBy('applied_at', 'desc');
        }])->findOrFail($id);

        // Calculs de performance à la volée
        $totalObjectives = $user->objectives->count();
        $achievedObjectives = $user->objectives->where('status', 'achieved')->count();
        $failedObjectives = $user->objectives->where('status', 'failed')->count();

        $successRate = $totalObjectives > 0 ? round(($achievedObjectives / $totalObjectives) * 100) : 0;

        // Somme des pénalités financières actives
        $totalPenalties = $user->sanctions->where('is_active', true)->sum('financial_penalty_amount');

        return view('admin.modules.user-profile', compact('user', 'successRate', 'totalObjectives', 'achievedObjectives', 'failedObjectives', 'totalPenalties'));
    }
}
