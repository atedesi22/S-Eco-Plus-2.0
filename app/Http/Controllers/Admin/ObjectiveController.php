<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Objective;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Http\Request;

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

        $objectives = $query->paginate(10);
        $staffMembers = User::all(); // Pour le formulaire d'attribution

        return view('admin.objectives.index', compact('objectives', 'staffMembers', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'target_value' => 'required|numeric|min:0',
            'period' => 'required|in:day,week,month,quarter,semester,year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $validated['current_value'] = 0;
        $validated['status'] = 'in_progress';

        Objective::create($validated);

        return redirect()->back()->with('success', 'Objectif assigné avec succès.');
    }

    public function storeSanction(Request $request, $id)
    {
        $objective = Objective::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'severity' => 'required|in:low,medium,high',
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

        // Optionnel : Basculer automatiquement le statut de l'objectif en cas de sanction majeure
        $objective->update(['status' => 'failed']);

        return redirect()->back()->with('success', 'Sanction enregistrée et appliquée au collaborateur.');
    }
}
