<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\Objective;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgencyDirectorDashboardController extends Controller
{
    //
    /**
     * Tableau de bord principal du Directeur d'Agence
     */
    public function index()
    {
        $director = Auth::user();
        $agencyId = $director->structure_id;
        $today = Carbon::today();

        // 1. KPI Financiers Globaux de l'Agence
        $totalClients = User::where('structure_id', $agencyId)
            ->role('Client')
            ->count();

        $totalEpargneClients = DB::table('accounts')
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->where('users.structure_id', $agencyId)
            ->sum('accounts.balance');

        // Synthèse financière du jour (Dépôts, Retraits, Frais) en une seule passe ou requêtes cibléee
        $dailyStats = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->where('users.structure_id', $agencyId)
            ->whereDate('transactions.created_at', $today)
            ->selectRaw("
                SUM(CASE WHEN transactions.type = 'deposit' THEN transactions.amount ELSE 0 END) as total_depots,
                SUM(CASE WHEN transactions.type = 'withdrawal' THEN transactions.amount ELSE 0 END) as total_retraits,
                SUM(transactions.fees) as total_frais
            ")->first();

        $depotsAujourdhui  = $dailyStats->total_depots ?? 0;
        $retraitsAujourdhui = $dailyStats->total_retraits ?? 0;
        $fraisAujourdhui    = $dailyStats->total_frais ?? 0;

        // 2. État des Caisses & Coffres-forts de l'agence
        $caisses = DB::table('caisses')
            ->leftJoin('users', 'caisses.assigned_to', '=', 'users.id')
            ->where('caisses.structure_id', $agencyId)
            ->select('caisses.*', 'users.name as agent_name')
            ->orderByRaw("CASE WHEN caisses.type = 'coffre_fort' THEN 1 ELSE 2 END")
            ->get();

        $liquiditeCaisses = $caisses->sum('current_balance');

        // 3. Performance des Zones de Collecte (Requête agrégée 0(1) performance)
        $zonesPerformance = Zone::where('zones.structure_id', $agencyId)
            ->leftJoin('users', function ($join) {
                $join->on('users.zone_id', '=', 'zones.id')
                    ->whereNull('users.deleted_at');
            })
            ->leftJoin('accounts', 'accounts.user_id', '=', 'users.id')
            ->leftJoin('transactions', function ($join) use ($today) {
                $join->on('transactions.account_id', '=', 'accounts.id')
                    ->where('transactions.type', '=', 'deposit')
                    ->whereDate('transactions.created_at', $today);
            })
            ->select(
                'zones.id',
                'zones.name',
                DB::raw("COUNT(DISTINCT CASE WHEN users.id IS NOT NULL THEN users.id END) as total_clients"),
                DB::raw("COALESCE(SUM(transactions.amount), 0) as collecte_jour")
            )
            ->groupBy('zones.id', 'zones.name')
            ->get();

        // 4. Demandes en attente de validation
        $validationsEnAttente = DB::table('pending_validations')
            ->join('users', 'pending_validations.requested_by', '=', 'users.id')
            ->where('pending_validations.structure_id', $agencyId)
            ->where('pending_validations.status', 'pending')
            ->select('pending_validations.*', 'users.name as requester_name')
            ->orderBy('pending_validations.created_at', 'desc')
            ->take(5)
            ->get();

        // 5. Flux Financier Récent
        $recentTransactions = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->join('users as clients', 'accounts.user_id', '=', 'clients.id')
            ->join('users as agents', 'transactions.performed_by', '=', 'agents.id')
            ->where('clients.structure_id', $agencyId)
            ->select(
                'transactions.*',
                'clients.name as client_name',
                'agents.name as agent_name',
                'accounts.type as account_type'
            )
            ->orderBy('transactions.created_at', 'desc')
            ->take(8)
            ->get();

        // Staff Global
        $totalAgents = User::where('structure_id', $agencyId)
            ->role(['Caissier', 'Comptable', 'Collectrice', 'Commercial'])
            ->count();

        return view('directeur.dashboard', compact(
            'totalClients',
            'totalAgents',
            'totalEpargneClients',
            'liquiditeCaisses',
            'depotsAujourdhui',
            'retraitsAujourdhui',
            'fraisAujourdhui',
            'caisses',
            'zonesPerformance',
            'validationsEnAttente',
            'recentTransactions'
        ));
    }

    /**
     * Affichage de la liste des demandes en attente de validation par le Directeur d'Agence
     */
    public function validationsIndex(Request $request)
    {
        $director = Auth::user();
        $agencyId = $director->structure_id;

        $query = DB::table('pending_validations')
            ->join('users', 'pending_validations.requested_by', '=', 'users.id')
            ->where('pending_validations.structure_id', $agencyId)
            ->select('pending_validations.*', 'users.name as requester_name');

        // Filtrage par statut (par défaut: 'pending')
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('pending_validations.status', $request->status);
        } else {
            $query->where('pending_validations.status', 'pending');
        }

        $validations = $query->orderBy('pending_validations.created_at', 'desc')->paginate(15);

        return view('directeur.validations.index', compact('validations'));
    }


    /**
     * 1. Caisses & Coffres
     * Gestion du solde des caisses de l'agence, des coffres-forts et détection des écarts.
     */
    // public function caissesIndex(Request $request, $agencyId)
    public function caissesIndex(Request $request = null)
    {
        $director = Auth::user();
        $agencyId = $director->structure_id;

        $caisses = Caisse::select('caisses.*', 'users.name as agent_name')
        ->leftJoin('users', 'caisses.assigned_to', '=', 'users.id')
        ->where('caisses.structure_id', $agencyId) // 🟢 Précisé avec caisses.
        ->orderByRaw("FIELD(caisses.type, 'coffre_fort', 'guichet', 'virtuelle')")
        ->get();

        // Caissiers disponibles
        $caissiers = User::where('structure_id', $agencyId)
            ->role(['Caissier', 'Comptable'])
            ->get();

        $soldeTotalCoffres = $caisses->where('type', 'coffre_fort')->sum('current_balance');
        $soldeTotalGuichets = $caisses->where('type', 'guichet')->sum('current_balance');

        return view('directeur.caisses.index', compact(
            'caisses',
            'caissiers',
            'soldeTotalCoffres',
            'soldeTotalGuichets'
        ));
    }

    /**
     * Ajouter un nouveau guichet / coffre
     */
    public function caissesStore(Request $request)
    {
        $director = Auth::user();

        // 1. Validation conforme au modèle
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:guichet,coffre_fort,virtuelle',
            'assigned_to'     => 'nullable|exists:users,id',
            'opening_balance' => 'required|numeric|min:0',
            'max_limit'       => 'required|numeric|min:0',
            'status'          => 'required|in:open,closed',
        ]);

        // 2. Uniformisation des données
        $validated['structure_id'] = $director->structure_id;
        $validated['current_balance'] = $validated['opening_balance']; // Le solde initial devient le solde courant à la création

        // 3. Création uniforme
        Caisse::create($validated);

        return back()->with('success', 'Nouvelle caisse / coffre créé avec succès.');
    }

    /**
     * Assigner un caissier à une caisse
     */
    public function caissesAssign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $caisse = Caisse::findOrFail($id);
        $caisse->update([
            'assigned_to' => $request->assigned_to
        ]);

        return back()->with('success', 'Affectation du caissier mise à jour.');
    }

    /**
     * Transfert Inter-Caisse (Délestage / Approvisionnement)
     */
    public function caissesTransfer(Request $request)
    {
        $request->validate([
            'from_caisse_id' => 'required|exists:caisses,id',
            'to_caisse_id'   => 'required|exists:caisses,id|different:from_caisse_id',
            'amount'         => 'required|numeric|min:1000',
        ]);

        $from = Caisse::findOrFail($request->from_caisse_id);

        if ($from->current_balance < $request->amount) {
            return back()->with('error', 'Solde insuffisant dans la caisse source.');
        }

        DB::transaction(function () use ($request, $from) {
            // Débit
            $from->decrement('current_balance', $request->amount);

            // Crédit
            Caisse::where('id', $request->to_caisse_id)
                ->increment('current_balance', $request->amount);

            // Traçabilité / Validation
            DB::table('pending_validations')->insert([
                'structure_id' => Auth::user()->structure_id,
                'requested_by' => Auth::id(),
                'type'         => 'cash_transfer',
                'description'  => "Transfert interne de " . number_format($request->amount, 0, ',', ' ') . " XAF",
                'amount'       => $request->amount,
                'status'       => 'approved',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });

        return back()->with('success', 'Virement inter-caisses exécuté avec succès.');
    }

    /**
     * 2. Zones de Collecte
     * Performance des zones géographiques et des collecteurs sur le terrain.
     */
    public function zonesIndex(Request $request)
    {
        $user = Auth::user();

        // 1. On récupère la clé de l'agence/structure de l'utilisateur connecté
        // (Ajustez 'structure_id' si le champ sur l'utilisateur s'appelle différemment)
        $structureId = $user->structure_id ?? $user->agency_id;

        // Récupérer la liste des agents de collecte de l'agence pour la vue (modale)
        $collectors = User::where('structure_id', $structureId)
            ->role('Collectrice') // Décommentez si vous utilisez Spatie/Roles
            ->select('id', 'name', 'phone')
            ->get();


        // 2. Sécurité : Si l'utilisateur n'a pas de structure/agence assignée
        if (!$structureId) {
            return view('directeur.zones.index', [
                'total_zones' => 0,
                'totalCollectedToday' => 0,
                'zones' => collect([]),
                'collectors' => collect([]),
                'error' => 'Aucune agence/structure n\'est assignée à votre compte.'
            ]);
        }

        $today = Carbon::today();

        // 3. On filtre par 'structure_id' au lieu de 'agency_id'
        $zones = Zone::where('structure_id', $structureId) // <-- Modification ici
            ->withCount(['clients as active_clients_count'])
            ->with(['manager:id,name,phone'])
            ->get()
            ->map(function ($zone) use ($today) {

                $collectedToday = 0;

                // Si un agent de collecte est affecté à cette zone
                if ($zone->collector_id) {
                    // On somme les dépôts réalisés aujourd'hui par cet agent
                    $collectedToday = DB::table('transactions')
                        ->where('performed_by', $zone->collector_id) // Agent de la zone
                        ->where('type', 'deposit')                   // Type d'opération (ex: dépôt)
                        ->whereDate('created_at', $today)            // Date du jour
                        ->sum('amount');
                }

                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'manager' => $zone->manager ? $zone->manager->name : 'Non assigné',
                    'active_clients' => $zone->active_clients_count,
                    'collected_today' => $collectedToday,
                    'monthly_target' => $zone->monthly_target ?? 0,
                    'completion_rate' => ($zone->monthly_target ?? 0) > 0
                        ? round(($collectedToday / $zone->monthly_target) * 100, 2)
                        : 0,
                ];
            });

        return view('directeur.zones.index', [
            'total_zones' => $zones->count(),
            'totalCollectedToday' => $zones->sum('collected_today'),
            'zones' => $zones,
            'collectors' => $collectors
        ]);
    }

    public function zonesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'monthly_target' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $structureId = $user->structure_id ?? $user->agency_id;

        // 1. Génération d'un code unique automatique (ex: ZN-B8A2F)
        $code = 'ZN-' . strtoupper(Str::random(5));

        // 2. Enregistrement en base de données
        Zone::create([
            'code' => $code, // <-- Champ obligatoire fourni ici
            'name' => $request->name,
            'description' => $request->description ?? null,
            'structure_id' => $structureId, // <-- Renseigne la structure parente
            'manager_id' => $request->manager_id,
            'monthly_target' => $request->monthly_target ?? 0,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Zone de collecte créée avec succès.');
    }

    public function zonesAssign(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'manager_id' => 'required|exists:users,id',
        ]);

        $zone = Zone::findOrFail($request->zone_id);
        $zone->update(['manager_id' => $request->manager_id]);

        return redirect()->back()->with('success', 'Agent affecté à la zone avec succès.');
    }

    /**
     * 3. Ressources Humaines & Personnel de l'Agence
     * Effectif, présence/absence et charge de travail par agent.
     */
    public function personnelIndex(Request $request, $agencyId)
    {
        $today = Carbon::today();

        // Répartition par rôle dans l'agence
        $staffByRole = User::where('agency_id', $agencyId)
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        // Statut de présence aujourd'hui
        $attendance = DB::table('attendances')
            ->where('agency_id', $agencyId)
            ->whereDate('date', $today)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Charge de travail des gestionnaires de portefeuille (Loan Officers)
        $loanOfficersWorkload = User::where('agency_id', $agencyId)
            ->where('role', 'loan_officer')
            ->withCount(['managedClients as total_clients', 'activeLoans as total_active_loans'])
            ->get(['id', 'name', 'email']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_staff' => User::where('agency_id', $agencyId)->count(),
                'staff_by_role' => $staffByRole,
                'today_attendance' => [
                    'present' => $attendance->get('present', 0),
                    'absent' => $attendance->get('absent', 0),
                    'on_leave' => $attendance->get('on_leave', 0),
                ],
                'loan_officers_workload' => $loanOfficersWorkload
            ]
        ]);
    }

    /**
     * 4. Objectifs & Performance Commerciale
     * Taux de réalisation des objectifs mensuels de l'agence.
     */
    public function getGoalsAndPerformance(Request $request, $agencyId)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $objectives = Objective::where('agency_id', $agencyId)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get()
            ->map(function ($obj) {
                $achievementPercentage = $obj->target_value > 0
                    ? min(round(($obj->current_value / $obj->target_value) * 100, 2), 100)
                    : 0;

                return [
                    'metric_name' => $obj->title, // Ex: Nouveaux comptes, Volume de crédits accordés
                    'target_value' => $obj->target_value,
                    'current_value' => $obj->current_value,
                    'unit' => $obj->unit, // Ex: FCFA, Clients, dossiers
                    'achievement_percentage' => $achievementPercentage,
                    'status' => $achievementPercentage >= 100 ? 'reached' : ($achievementPercentage >= 75 ? 'on_track' : 'at_risk'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => Carbon::now()->translatedFormat('F Y'),
                'objectives' => $objectives
            ]
        ]);
    }

    /**
     * 5. Clients & Portefeuille
     * Segmentation des clients et suivi du portefeuille de crédit (PAR - Portfolio At Risk).
     */
    public function getClientPortfolioSummary(Request $request, $agencyId)
    {
        // Statut des clients de l'agence
        $clientsStats = Client::where('agency_id', $agencyId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Analyse du portefeuille de crédits
        $loans = Loan::where('agency_id', $agencyId)->where('status', 'active')->get();

        $totalOutstandingCapital = $loans->sum('outstanding_amount'); // Capital restant dû

        // Portefeuille à risque (PAR) : Crédits en retard
        $par30 = $loans->where('days_in_arrears', '>', 30)->where('days_in_arrears', '<=', 90)->sum('outstanding_amount');
        $par90 = $loans->where('days_in_arrears', '>', 90)->sum('outstanding_amount');

        $par30Ratio = $totalOutstandingCapital > 0 ? round(($par30 / $totalOutstandingCapital) * 100, 2) : 0;
        $par90Ratio = $totalOutstandingCapital > 0 ? round(($par90 / $totalOutstandingCapital) * 100, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'clients' => [
                    'total' => $clientsStats->sum(),
                    'active' => $clientsStats->get('active', 0),
                    'inactive' => $clientsStats->get('inactive', 0),
                    'prospects' => $clientsStats->get('prospect', 0),
                ],
                'credit_portfolio' => [
                    'total_active_loans' => $loans->count(),
                    'total_outstanding_capital' => $totalOutstandingCapital,
                    'risk_analysis' => [
                        'par_30_amount' => $par30,
                        'par_30_ratio' => $par30Ratio, // % du portefeuille en retard de 30-90j
                        'par_90_amount' => $par90,
                        'par_90_ratio' => $par90Ratio, // % du portefeuille en retard > 90j (créances douteuses)
                    ]
                ]
            ]
        ]);
    }
}
