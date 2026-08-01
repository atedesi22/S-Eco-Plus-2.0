<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Caisse;
use App\Models\CashTransaction;
use App\Models\Objective;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
    public function personnelIndex(Request $request)
    {
        $user = Auth::user();

        // Détection de la colonne (structure_id ou agency_id)
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        if (!$agencyId) {
            return view('directeur.personnel.index', [
                'total_staff' => 0,
                'staff_by_role' => collect([]),
                'today_attendance' => ['present' => 0, 'absent' => 0, 'on_leave' => 0],
                'staff' => collect([]),
                'roles' => collect([])
            ]);
        }

        $today = Carbon::today();

        // Récupérer uniquement les utilisateurs de la structure qui NE SONT PAS des clients
        $staffQuery = User::where($agencyColumn, $agencyId)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Client', 'client']);
            })
            ->with('roles');

        if (Schema::hasColumn('users', 'collector_id')) {
            $staffQuery->withCount(['managedClients as total_clients']);
        }

        $staff = $staffQuery->get();

        // Compter le personnel par rôle
        $staffByRole = $staff->flatMap(function ($member) {
            return $member->roles->pluck('name');
        })->countBy();

        // Statut de présence
        $attendance = collect([]);
        if (Schema::hasTable('attendances')) {
            $attendanceQuery = DB::table('attendances')->whereDate('date', $today);

            if (Schema::hasColumn('attendances', $agencyColumn)) {
                $attendanceQuery->where($agencyColumn, $agencyId);
            }

            $attendance = $attendanceQuery
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');
        }

        // Récupère les rôles du personnel
        $roles = Role::whereNotIn('name', ['Client', 'SuperAdmin', 'Directeur Regional', 'PDG', 'DG', 'DAF', 'DOM'])->get();

        return view('directeur.personnel.index', [
            'total_staff' => $staff->count(),
            'staff_by_role' => $staffByRole,
            'today_attendance' => [
                'present' => $attendance->get('present', 0),
                'absent' => $attendance->get('absent', 0),
                'on_leave' => $attendance->get('on_leave', 0),
            ],
            'staff' => $staff,
            'roles' => $roles
        ]);
    }

    public function personnelStore(Request $request)
    {
        $user = Auth::user();

        // Détection dynamique de la colonne de rattachement
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string' // Nom du rôle Spatie (ex: collector, cashier, loan_officer)
        ]);

        // 1. Création de l'utilisateur
        $newMember = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            $agencyColumn => $agencyId,
            'password' => Hash::make('password123') // Mot de passe temporaire par défaut
        ]);

        // 2. Assignation du rôle Spatie
        $newMember->assignRole($request->role);

        return redirect()->back()->with('success', 'Membre du personnel ajouté avec succès.');
    }

    /**
     * 4. Objectifs & Performance Commerciale
     * Taux de réalisation des objectifs mensuels de l'agence.
     */
    public function performanceIndex()
    {
        $user = Auth::user();
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        // Liste des agents de l'agence (hors clients et rôles de direction)
        $staff = User::where($agencyColumn, $agencyId)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Client', 'client', 'PDG', 'DG', 'DAF', 'DOM']);
            })->get();

        // Rôles disponibles pour assigner un objectif global par rôle
        $roles = Role::whereNotIn('name', ['Client', 'SuperAdmin', 'PDG', 'DG', 'DAF', 'DOM', 'Directeur Regional'])->get();

        // Récupérer les objectifs créés pour cette agence (soit attribués à un agent de l'agence, soit généraux)
        $staffIds = $staff->pluck('id')->toArray();

        $objectives = Objective::whereIn('user_id', $staffIds)
            ->orWhereNotNull('role_name')
            ->with(['user'])
            ->latest()
            ->get();

        // Calcul des métriques globales pour les KPI cards
        $totalObjectives = $objectives->count();
        $achievedCount = $objectives->filter(fn($obj) => $obj->current_value >= $obj->target_value)->count();
        $inProgressCount = $totalObjectives - $achievedCount;

        return view('directeur.performance.index', compact(
            'objectives',
            'staff',
            'roles',
            'totalObjectives',
            'achievedCount',
            'inProgressCount'
        ));
    }

    public function performanceStoreObjective(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'assignment_type' => 'required|in:agent,role',
            'user_id' => 'nullable|required_if:assignment_type,agent|exists:users,id',
            'role_name' => 'nullable|required_if:assignment_type,role|string',
            'type' => 'required|in:collecte_amount,new_accounts,product_sales,credit_recovery',
            'target_value' => 'required|numeric|min:1',
            'period' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Objective::create([
            'title' => $request->title,
            'user_id' => $request->assignment_type === 'agent' ? $request->user_id : null,
            'role_name' => $request->assignment_type === 'role' ? $request->role_name : null,
            'type' => $request->type,
            'target_value' => $request->target_value,
            'period' => $request->period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'in_progress',
        ]);

        return redirect()->back()->with('success', 'L\'objectif a été assigné avec succès !');
    }

    public function performanceDestroyObjective(Objective $objective)
    {
        $objective->delete();
        return redirect()->back()->with('success', 'L\'objectif a été supprimé.');
    }

    public function clientsIndex(Request $request)
    {
        $user = Auth::user();
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        // Récupérer tous les clients rattachés à l'agence du Directeur
        $clientsQuery = User::role('Client')
            ->where($agencyColumn, $agencyId)
            ->with(['accounts', 'collector']);

        $clients = $clientsQuery->latest()->get();

        // Statistiques globales du portefeuille
        $totalClients = $clients->count();
        $activeClients = $clients->where('status', 'active')->count();

        // Calcul du solde total épargné dans l'agence
        $totalSavings = Account::whereIn('user_id', $clients->pluck('id'))->sum('balance');

        return view('directeur.clients.index', compact(
            'clients',
            'totalClients',
            'activeClients',
            'totalSavings'
        ));
    }

    public function produitIndex()
    {
        $products = Product::latest()->get();

        $totalProducts = $products->count();
        $totalStock = $products->sum('stock');
        $lowStockCount = $products->filter(fn($p) => $p->stock <= $p->alert_threshold)->count();

        return view('directeur.articles.index', compact(
            'products',
            'totalProducts',
            'totalStock',
            'lowStockCount'
        ));
    }

    public function produitStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price_cash' => 'required|numeric|min:0',
            'selling_price_installment' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:0',
            'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $reference = 'PRD-' . date('Y') . '-' . strtoupper(Str::random(5));

        $primaryImagePath = null;
        if ($request->hasFile('primary_image')) {
            $primaryImagePath = $request->file('primary_image')->store('products', 'public');
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('products/gallery', 'public');
            }
        }

        Product::create([
            'reference' => $reference,
            'name' => $request->name,
            'description' => $request->description,
            'purchase_price' => $request->purchase_price,
            'selling_price_cash' => $request->selling_price_cash,
            'selling_price_installment' => $request->selling_price_installment,
            'stock' => $request->stock,
            'alert_threshold' => $request->alert_threshold,
            'is_available' => $request->stock > 0,
            'primary_image' => $primaryImagePath,
            'gallery_images' => $galleryPaths,
        ]);

        return redirect()->back()->with('success', 'Article ajouté au catalogue avec succès.');
    }

    public function produitUpdate(Request $request, Product $article)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price_cash' => 'required|numeric|min:0',
            'selling_price_installment' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:0',
            'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('primary_image')) {
            if ($article->primary_image) {
                Storage::disk('public')->delete($article->primary_image);
            }
            $article->primary_image = $request->file('primary_image')->store('products', 'public');
        }

        $article->update([
            'name' => $request->name,
            'description' => $request->description,
            'purchase_price' => $request->purchase_price,
            'selling_price_cash' => $request->selling_price_cash,
            'selling_price_installment' => $request->selling_price_installment,
            'stock' => $request->stock,
            'alert_threshold' => $request->alert_threshold,
            'is_available' => $request->has('is_available') ? true : ($request->stock > 0),
        ]);

        return redirect()->back()->with('success', 'Article mis à jour avec succès.');
    }

    public function produitDestroy(Product $article)
    {
        if ($article->primary_image) {
            Storage::disk('public')->delete($article->primary_image);
        }
        if ($article->gallery_images) {
            foreach ($article->gallery_images as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $article->delete();

        return redirect()->back()->with('success', 'Article retiré du catalogue.');
    }

    public function shopIndex(Request $request)
    {
        $user = Auth::user();
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        // Charger les commandes des clients rattachés à cette agence
        $orders = Order::whereHas('client', function ($q) use ($agencyColumn, $agencyId) {
                $q->where($agencyColumn, $agencyId);
            })
            ->with(['client', 'product', 'collector'])
            ->latest()
            ->get();

        // Calculs des KPIs
        $totalOrders = $orders->count();
        $pendingDeliveries = $orders->where('status', 'eligible_for_delivery')->count(); // Seuil 60% atteint mais pas encore validé
        $totalCollected = $orders->sum('paid_amount');
        $overdueOrders = $orders->filter(function ($order) {
            // En retard si livré (dépassé 60%) mais pas de versement depuis +14 jours
            return $order->status === 'delivered' && $order->last_payment_at && $order->last_payment_at->diffInDays(now()) > 14;
        })->count();

        return view('directeur.commandes.index', compact(
            'orders',
            'totalOrders',
            'pendingDeliveries',
            'totalCollected',
            'overdueOrders'
        ));
    }

    /**
     * Valide l'accord de livraison d'un article une fois le seuil des 60% franchi.
     */
    public function approveDelivery(Order $order)
    {
        if ($order->paid_amount < $order->threshold_60_amount) {
            return redirect()->back()->with('error', 'Le montant versé n\'atteint pas encore le seuil requis de 60%.');
        }

        $order->update([
            'delivered_approved_by_director' => true,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Livraison approuvée ! Le bon de sortie de stock a été généré.');
    }

    /**
     * Envoie un ordre de relance au collecteur pour relancer le client sur les 40% restants.
     */
    public function remindAgent(Request $request, Order $order)
    {
        $request->validate([
            'note' => 'nullable|string|max:255'
        ]);

        // Ici, tu peux enregistrer une notification en BDD pour l'agent/collecteur
        // Notification::send($order->collector, new DirectAgentReminderNotification($order, $request->note));

        return redirect()->back()->with('success', 'Rappel direct transmis avec succès au collecteur ' . ($order->collector->name ?? 'référent') . '.');
    }

    public function repportIndex(Request $request)
    {
        $user = Auth::user();
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        // Date sélectionnée (par défaut aujourd'hui)
        $selectedDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        // 1. Encaissements de la journée via les tranches de commande/épargne
        $paymentsToday = OrderPayment::whereDate('created_at', $selectedDate)
            ->whereHas('collector', function ($q) use ($agencyColumn, $agencyId) {
                $q->where($agencyColumn, $agencyId);
            })
            ->with(['order.client', 'collector'])
            ->get();

        $totalFieldCollected = $paymentsToday->sum('amount');

        // 2. Ventes cash boutique conclues la journée
        $cashSalesToday = Order::whereDate('created_at', $selectedDate)
            ->where('payment_type', 'cash')
            ->whereHas('client', function ($q) use ($agencyColumn, $agencyId) {
                $q->where($agencyColumn, $agencyId);
            })
            ->get();

        $totalCashSales = $cashSalesToday->sum('total_amount');
        $grandTotalCollected = $totalFieldCollected + $totalCashSales;

        // 3. Nouveaux clients enregistrés la journée
        $newClientsCount = User::role('Client')
            ->where($agencyColumn, $agencyId)
            ->whereDate('created_at', $selectedDate)
            ->count();

        // 4. Livraisons effectuées / approuvées la journée (seuil 60% validé)
        $deliveriesCount = Order::whereDate('delivered_at', $selectedDate)
            ->whereHas('client', function ($q) use ($agencyColumn, $agencyId) {
                $q->where($agencyColumn, $agencyId);
            })
            ->count();

        // 5. Performance par collecteur pour la journée
        $collectorsPerformance = User::role(['Collectrice', 'Commercial'])
            ->where($agencyColumn, $agencyId)
            ->get()
            ->map(function ($collector) use ($selectedDate) {
                $amount = OrderPayment::where('collected_by', $collector->id)
                    ->whereDate('created_at', $selectedDate)
                    ->sum('amount');
                $count = OrderPayment::where('collected_by', $collector->id)
                    ->whereDate('created_at', $selectedDate)
                    ->count();

                return [
                    'collector' => $collector,
                    'total_amount' => $amount,
                    'transactions_count' => $count,
                ];
            })
            ->sortByDesc('total_amount');

        return view('directeur.rapports.index', compact(
            'selectedDate',
            'paymentsToday',
            'totalFieldCollected',
            'totalCashSales',
            'grandTotalCollected',
            'newClientsCount',
            'deliveriesCount',
            'collectorsPerformance'
        ));
    }


    public function fluxIndex(Request $request)
    {
        $user = Auth::user();
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        $query = CashTransaction::where('agency_id', $agencyId)
            ->with(['user', 'operator'])
            ->latest();

        // Filtre par type (Crédit / Débit)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par catégorie de flux
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filtre par plage de dates
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtre par recherche textuelle (Numéro transaction, nom client/opérateur)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('operator', fn($o) => $o->where('name', 'like', "%{$search}%"));
            });
        }

        // Statistiques globales de la période filtrée
        $totalCredits = (clone $query)->where('type', 'credit')->sum('amount');
        $totalDebits  = (clone $query)->where('type', 'debit')->sum('amount');
        $netCashFlow  = $totalCredits - $totalDebits;

        $transactions = $query->paginate(20)->withQueryString();

        return view('directeur.flux.index', compact(
            'transactions',
            'totalCredits',
            'totalDebits',
            'netCashFlow'
        ));
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
