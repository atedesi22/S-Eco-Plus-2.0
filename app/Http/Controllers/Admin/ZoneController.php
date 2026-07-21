<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Création d'une nouvelle Zone / Secteur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:zones,code',
            'structure_id' => 'required|exists:structures,id',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $zone = Zone::create($validated);

        // Si un Chef de Zone est assigné lors de la création, on met à jour son zone_id
        if ($request->filled('manager_id')) {
            User::where('id', $request->manager_id)->update(['zone_id' => $zone->id]);
        }

        return redirect()->back()->with('success', 'Zone de collecte créée avec succès.');
    }

    /**
     * Affectation ou transfert d'agents (Collectrices / Commerciaux) vers une zone
     */
    public function assignAgents(Request $request, $zoneId)
    {
        $request->validate([
            'agent_ids' => 'required|array',
            'agent_ids.*' => 'exists:users,id',
        ]);

        // Attribue la zone sélectionnée aux agents choisis
        User::whereIn('id', $request->agent_ids)->update(['zone_id' => $zoneId]);

        return redirect()->back()->with('success', 'Agents affectés à la zone avec succès.');
    }
}
