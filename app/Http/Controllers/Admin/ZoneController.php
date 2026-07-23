<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'is_active' => 'boolean',
        ]);

        $commercials = User::role(['Chef Commercial'])->get();

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

    /**
     * Détails d'une zone avec statistiques et agents
     */
    public function show($id)
    {
        // 1. Récupération de la zone avec son agence, son manager et le personnel
        $zone = Zone::with(['agency', 'manager', 'members'])->findOrFail($id);

        // 2. Séparation des agents (Commerciaux et Collectrices)
        $commerciaux = $zone->members()->role('Commercial')->get();
        $collectrices = $zone->members()->role('Collectrice')->get();
        $agentsTerrain = $zone->members()->role(['Commercial', 'Collectrice'])->get();

        // 3. Récupération des chefs de zone potentiels pour la modale d'édition
        $potentialManagers = User::role('Chef Commercial')
            ->where(function ($q) use ($zone) {
                $q->where('structure_id', $zone->structure_id)
                  ->orWhereNull('structure_id');
            })->get();

        // 4. Calcul du flux de transactions (Dépôts & Retraits)
        // Global pour la zone
        $statsGlobales = DB::table('transactions')
            ->join('users', 'transactions.performed_by', '=', 'users.id')
            ->where('users.zone_id', $zone->id)
            ->select(
                DB::raw("SUM(CASE WHEN transactions.type = 'deposit' THEN transactions.amount ELSE 0 END) as total_depots"),
                DB::raw("SUM(CASE WHEN transactions.type = 'withdrawal' THEN transactions.amount ELSE 0 END) as total_retraits")
            )->first();

        // Par agent de terrain dans la zone
        $agentsWithStats = $agentsTerrain->map(function ($agent) {
            $stats = DB::table('transactions')
                ->where('performed_by', $agent->id) // Transactions effectuées par cet agent
                ->select(
                    DB::raw("SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as total_depots"),
                    DB::raw("SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END) as total_retraits")
                )->first();

            $agent->total_depots = $stats->total_depots ?? 0;
            $agent->total_retraits = $stats->total_retraits ?? 0;
            return $agent;
        });

        $statsGlobalesClients = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->where('users.zone_id', $zone->id)
            ->select(
                DB::raw("SUM(CASE WHEN transactions.type = 'depot' THEN transactions.amount ELSE 0 END) as total_depots"),
                DB::raw("SUM(CASE WHEN transactions.type = 'retrait' THEN transactions.amount ELSE 0 END) as total_retraits")
            )->first();

        return view('admin.zones.show', compact(
            'zone',
            'commerciaux',
            'collectrices',
            'agentsWithStats',
            'potentialManagers',
            'statsGlobalesClients',
            'statsGlobales'
        ));
    }

    /**
     * Mettre à jour la zone (Renommer / Changer le chef de zone)
     */
    public function update(Request $request, $id)
    {
        $zone = Zone::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:zones,code,' . $zone->id,
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $zone->update($validated);

        // Mettre à jour la zone du nouveau manager si nécessaire
        if ($request->filled('manager_id')) {
            User::where('id', $request->manager_id)->update(['zone_id' => $zone->id]);
        }

        return redirect()->back()->with('success', 'Paramètres de la zone mis à jour avec succès.');
    }

    /**
     * Supprimer définitivement la zone
     */
    public function destroy($id)
    {
        $zone = Zone::findOrFail($id);

        // Détacher les utilisateurs associés à cette zone avant suppression
        User::where('zone_id', $zone->id)->update(['zone_id' => null]);

        $structureId = $zone->structure_id;
        $zone->delete();

        return redirect()->route('admin.structures.index')
            ->with('success', 'La zone de collecte a été supprimée avec succès.');
    }
}
