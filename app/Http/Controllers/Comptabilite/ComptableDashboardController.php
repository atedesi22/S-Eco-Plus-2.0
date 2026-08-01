<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Order;
use App\Models\Product;
use App\Models\Structure;
use App\Models\SubAccount;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ComptableDashboardController extends Controller
{
    //
    /**
     * Dashboard & Console Comptable personnalisée pour l'Agence de la Comptable
     */
    public function index()
    {
        $comptable = Auth::user();

        // 1. Récupérer l'agence du comptable
        $agency = Structure::with(['zones'])->findOrFail($comptable->structure_id);

        // 2. Récupérer tous les IDs des membres de cette agence (Agents + Clients)
        $agencyUserIds = User::where('structure_id', $agency->id)->pluck('id');

        // 3. Stats Financières Globales de l'Agence
        $statsFinancieres = DB::table('transactions')
            ->whereIn('performed_by', $agencyUserIds)
            ->select(
                DB::raw("COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_depots"),
                DB::raw("COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_retraits")
            )->first();

        // 4. 10 Dernières Transactions au niveau de l'agence
        $recentTransactions = DB::table('transactions')
            ->leftJoin('users as agent', 'transactions.performed_by', '=', 'agent.id')
            ->leftJoin('users as client', 'transactions.account_id', '=', 'client.id')
            ->whereIn('transactions.performed_by', $agencyUserIds)
            ->orWhereIn('transactions.account_id', $agencyUserIds)
            ->select(
                'transactions.*',
                DB::raw("COALESCE(client.name, 'N/A') as client_name"),
                DB::raw("COALESCE(agent.name, 'Système') as agent_name")
            )
            ->orderBy('transactions.created_at', 'desc')
            ->take(10)
            ->get();

        // 5. Charger la liste des clients de l'agence avec détails des comptes et sous-comptes
        $clientsAgence = User::role('Client')
            ->where('structure_id', $agency->id)
            ->select('id', 'name', 'phone')
            ->get()
            ->map(function ($client) {
                // 1. Récupérer tous les comptes principaux du client
                $accounts = DB::table('accounts')->where('user_id', $client->id)->get();
                $accountIds = $accounts->pluck('id')->toArray();

                // Calcul du solde du compte principal
                $depotsMain = DB::table('transactions')
                    ->whereIn('account_id', $accountIds)
                    ->whereNull('sub_account_id')
                    ->where('type', 'deposit')
                    ->sum('amount');

                $retraitsMain = DB::table('transactions')
                    ->whereIn('account_id', $accountIds)
                    ->whereNull('sub_account_id')
                    ->where('type', 'withdrawal')
                    ->sum('amount');

                $fraisMain = DB::table('transactions')
                    ->whereIn('account_id', $accountIds)
                    ->whereNull('sub_account_id')
                    ->where('type', 'withdrawal')
                    ->sum('fees');

                $firstAccount = $accounts->first();
                $client->main_account_name = $firstAccount ? ($firstAccount->type ?? 'Tontine Principal') : 'Compte Non Trouvé';
                $client->main_balance = $depotsMain - ($retraitsMain + $fraisMain);

                // 2. 🟢 CORRECTION : Récupérer les sous-comptes via les account_ids
                $client->sub_accounts = DB::table('sub_accounts')
                    ->whereIn('account_id', $accountIds)
                    ->get()
                    ->map(function ($sub) {
                        $depotsSub = DB::table('transactions')->where('sub_account_id', $sub->id)->where('type', 'deposit')->sum('amount');
                        $retraitsSub = DB::table('transactions')->where('sub_account_id', $sub->id)->where('type', 'withdrawal')->sum('amount');
                        $fraisSub = DB::table('transactions')->where('sub_account_id', $sub->id)->where('type', 'withdrawal')->sum('fees');

                        // Calcul si balance est null/inexistant
                        $calculatedBalance = $depotsSub - ($retraitsSub + $fraisSub);
                        $sub->balance = (isset($sub->balance) && $sub->balance > 0) ? $sub->balance : $calculatedBalance;

                        return $sub;
                    });

                // 3. Calcul du Solde Total
                $subAccountsTotal = $client->sub_accounts->sum('balance');
                $client->total_balance = $client->main_balance + $subAccountsTotal;
                $client->balance = $client->total_balance;

                return $client;
            });

        return view('comptabilite.dashboard', compact(
            'agency',
            'statsFinancieres',
            'recentTransactions',
            'clientsAgence'
        ));
    }

    /**
     * Traitement des Dépôts et Retraits Express au Guichet
     */
    public function storeTransaction(Request $request)
    {
        // 1. Validation de la requête (incluant sub_account_id)
        $request->validate([
            'user_id'        => 'required|exists:users,id',
            'sub_account_id' => 'nullable|exists:sub_accounts,id',
            'type'           => 'required|in:deposit,withdrawal',
            'amount'         => 'required|numeric|min:100',
        ]);

        $comptable = Auth::user();
        $typeLabel = $request->type === 'deposit' ? 'Dépôt' : 'Retrait';

        // 2. Récupération du compte principal de l'utilisateur
        $account = Account::where('user_id', $request->user_id)->first();

        if (!$account) {
            return redirect()->back()->with('error', 'Erreur : Aucun compte bancaire/épargne associé à ce client.');
        }

        // 3. Calcul des frais de retrait (500 XAF par tranche de 25 000 XAF)
        $fees = 0;
        if ($request->type === 'withdrawal') {
            $tranches = ceil($request->amount / 25000);
            $fees = $tranches * 500;
        }

        $totalADebiter = $request->amount + $fees;

        // 4. Exécution de la transaction SQL
        try {
            DB::transaction(function () use ($request, $account, $comptable, $fees, $typeLabel, $totalADebiter) {

                $subAccountId = $request->input('sub_account_id');
                $subAccount = null;

                if ($subAccountId) {
                    // Charger le sous-compte et vérifier qu'il appartient bien au compte principal
                    $subAccount = SubAccount::where('id', $subAccountId)
                        ->where('account_id', $account->id)
                        ->firstOrFail();
                }

                // Vérification du solde en cas de retrait
                if ($request->type === 'withdrawal') {
                    $soldeDisponible = $subAccount ? $subAccount->balance : $account->balance;

                    if ($totalADebiter > $soldeDisponible) {
                        $nomCible = $subAccount ? 'le sous-compte (' . $subAccount->name . ')' : 'le compte principal';
                        throw new \Exception('Solde insuffisant sur ' . $nomCible . ' (' . number_format($soldeDisponible, 0, ',', ' ') . ' XAF disponible, frais inclus).');
                    }
                }

                // Mise à jour des soldes (Compte Principal vs Sous-Compte)
                if ($subAccount) {
                    if ($request->type === 'deposit') {
                        $subAccount->increment('balance', $request->amount);
                    } else {
                        $subAccount->decrement('balance', $totalADebiter);
                    }
                } else {
                    if ($request->type === 'deposit') {
                        $account->increment('balance', $request->amount);
                    } else {
                        $account->decrement('balance', $totalADebiter);
                    }
                }

                // Enregistrement dans l'historique des transactions
                $prefix = $request->type === 'deposit' ? 'DEP-' : 'RET-';

                DB::table('transactions')->insert([
                    'account_id'     => $account->id,
                    'sub_account_id' => $subAccount ? $subAccount->id : null, // Ne fonctionne que si la colonne existe en BD
                    'performed_by'   => $comptable->id,
                    'type'           => $request->type,
                    'amount'         => $request->amount,
                    'fees'           => $fees,
                    'reference'      => $prefix . strtoupper(uniqid()),
                    'description'    => $typeLabel . ' Express au Guichet ' . ($subAccount ? '(' . $subAccount->name . ')' : '(Compte principal)'),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            });

            return redirect()->back()->with('success', $typeLabel . ' de ' . number_format($request->amount, 0, ',', ' ') . ' XAF effectué avec succès !');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    /**
     * Suivi et Clôture des Caisses des Agents
     */
    public function caisses()
    {
        $comptable = Auth::user();
        $agencyId = $comptable->structure_id;

        // 1. Caisses des agents de l'agence avec calcul des soldes
        $agentsCaisses = User::where('structure_id', $agencyId)
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['Collectrice', 'Commercial', 'Caissier']);
            })
            ->with(['roles', 'zone'])
            ->get()
            ->map(function ($agent) {
                // Dépôts perçus par l'agent
                $agent->total_encaisse = DB::table('transactions')
                    ->where('performed_by', $agent->id)
                    ->where('type', 'deposit')
                    ->sum('amount');

                // Retraits payés par l'agent
                $agent->total_decaisse = DB::table('transactions')
                    ->where('performed_by', $agent->id)
                    ->where('type', 'withdrawal')
                    ->sum('amount');

                // Solde théorique que l'agent doit avoir physiquement en main
                $agent->solde_theorique = $agent->total_encaisse - $agent->total_decaisse;

                return $agent;
            });

        // 2. Calcul des totaux globaux pour les cartes de synthèse
        $totalEspecesCollectees = $agentsCaisses->sum('total_encaisse');
        $totalSoldeEnCirculation = $agentsCaisses->sum('solde_theorique');

        return view('comptabilite.caisses.index', compact(
            'agentsCaisses',
            'totalEspecesCollectees',
            'totalSoldeEnCirculation'
        ));
    }



    /**
     * Traitement de la validation du Versement / Déchargement d'un agent
     */
    public function validerArrete(Request $request)
    {
        $request->validate([
            'agent_id'       => 'required|exists:users,id',
            'amount_declare' => 'required|numeric|min:0',
        ]);

        $comptable = Auth::user();

        // 1. Récupérer le compte (Account) associé à l'agent
        $agentAccountId = DB::table('accounts')->where('user_id', $request->agent_id)->value('id');

        // Sécurité au cas où l'agent n'a pas encore de compte dans la table `accounts`
        if (!$agentAccountId) {
            return redirect()->back()->with('error', 'Erreur : Aucun compte associé à cet agent sur le système.');
        }

        // 2. Calcul des encaissements et décaissements via transactions.account_id
        $encaisse = DB::table('transactions')->where('account_id', $agentAccountId)->where('type', 'deposit')->sum('amount');
        $decaisse = DB::table('transactions')->where('account_id', $agentAccountId)->where('type', 'withdrawal')->sum('amount');
        $soldeTheorique = $encaisse - $decaisse;

        $ecart = $request->amount_declare - $soldeTheorique;

        // 3. Exécution sécurisée
        try {
            DB::transaction(function () use ($request, $comptable, $agentAccountId, $soldeTheorique, $ecart) {

                // A. Enregistrement de la clôture dans cash_settlements
                $arreteId = DB::table('cash_settlements')->insertGetId([
                    'agent_id'        => $request->agent_id,
                    'validated_by'    => $comptable->id,
                    'expected_amount' => $soldeTheorique,
                    'declared_amount' => $request->amount_declare,
                    'gap_amount'      => $ecart,
                    'status'          => $ecart == 0 ? 'conforme' : ($ecart < 0 ? 'manquant' : 'surplus'),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // B. Transfert de déchargement lié au compte de l'agent (account_id non null)
                DB::table('transactions')->insert([
                    'account_id'   => $agentAccountId, // ID de la table `accounts` de l'agent
                    'performed_by' => $comptable->id,
                    'type'         => 'transfer',
                    'amount'       => $request->amount_declare,
                    'fees'         => 0,
                    'reference'    => 'ARR-' . strtoupper(uniqid()),
                    'description'  => 'Déchargement caisse Agent ID: ' . $request->agent_id . ' (Arrêté #' . $arreteId . ')',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            });

            $msg = $ecart == 0
                ? 'Caisse déchargée et conforme !'
                : 'Arrêté enregistré avec un ' . ($ecart < 0 ? 'MANQUANT' : 'SURPLUS') . ' de ' . number_format(abs($ecart), 0, ',', ' ') . ' XAF.';

            return redirect()->back()->with($ecart == 0 ? 'success' : 'warning', $msg);

            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Erreur lors du déchargement : ' . $e->getMessage());
            }
    }



        /**
     * Consultation du Grand Livre et Historique des Écritures
     */
    public function ecritures(Request $request)
    {
        $comptable = Auth::user();
        $agency = Structure::findOrFail($comptable->structure_id);

        // Filtres
        $type = $request->get('type');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $search = $request->get('search');

        // Requête sur les transactions
        $query = DB::table('transactions')
            ->leftJoin('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->leftJoin('users as client', 'accounts.user_id', '=', 'client.id')
            ->leftJoin('users as agent', 'transactions.performed_by', '=', 'agent.id')
            ->select(
                'transactions.*',
                'client.name as client_name',
                'client.phone as client_phone',
                'agent.name as agent_name'
            )
            ->whereDate('transactions.created_at', '>=', $startDate)
            ->whereDate('transactions.created_at', '<=', $endDate);

        // Application des filtres dynamique
        if ($type) {
            $query->where('transactions.type', $type);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('transactions.reference', 'LIKE', "%{$search}%")
                ->orWhere('transactions.description', 'LIKE', "%{$search}%")
                ->orWhere('client.name', 'LIKE', "%{$search}%")
                ->orWhere('agent.name', 'LIKE', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')->paginate(20);

        // Totaux sur la période filtrée
        $totauxPériode = DB::table('transactions')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select(
                DB::raw("COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_depots"),
                DB::raw("COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_retraits"),
                DB::raw("COALESCE(SUM(fees), 0) as total_frais")
            )->first();

        return view('comptabilite.ecritures.index', compact(
            'transactions',
            'totauxPériode',
            'type',
            'startDate',
            'endDate',
            'search'
        ));
    }

    /**
     * Passer une Écriture Comptable Manuelle / Ajustement
     */
    public function storeEcriture(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'type'        => 'required|in:deposit,withdrawal,fee',
            'amount'      => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $comptable = Auth::user();
        $account = DB::table('accounts')->where('user_id', $request->user_id)->first();

        if (!$account) {
            return redirect()->back()->with('error', 'Aucun compte associé à cet utilisateur.');
        }

        DB::table('transactions')->insert([
            'account_id'   => $account->id,
            'performed_by' => $comptable->id,
            'type'         => $request->type,
            'amount'       => $request->amount,
            'fees'         => 0,
            'reference'    => 'REG-' . strtoupper(uniqid()),
            'description'  => '[Régularisation Comptable] ' . $request->description,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->back()->with('success', 'Écriture de régularisation enregistrée avec succès !');
    }

    /**
     * Vue principale de la gestion du Coffre-Fort Agence
     */
    public function coffre()
    {
        $comptable = Auth::user();
        $agencyId = $comptable->structure_id;

        // 1. Calcul du solde actuel du coffre
        // Entrées dans le coffre : Approvisionnements siège/banque + Arrêtés/Déchargements d'agents
        $totalEntrees = DB::table('transactions')
            ->whereNull('account_id') // Opérations internes agence
            ->whereIn('type', ['vault_deposit', 'transfer'])
            ->sum('amount');

        // Sorties du coffre : Dépôts en banque + Dotations de caisses aux agents
        $totalSorties = DB::table('transactions')
            ->whereNull('account_id')
            ->whereIn('type', ['vault_withdrawal', 'agent_dotation'])
            ->sum('amount');

        $soldeCoffre = $totalEntrees - $totalSorties;

        // 2. Historique des mouvements du coffre
        $mouvementsCoffre = DB::table('transactions')
            ->leftJoin('users as agent', 'transactions.performed_by', '=', 'agent.id')
            ->select(
                'transactions.*',
                'agent.name as agent_name'
            )
            ->whereNull('transactions.account_id')
            ->whereIn('transactions.type', ['vault_deposit', 'vault_withdrawal', 'agent_dotation', 'transfer'])
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(15);

        // 3. Liste des agents de l'agence pour la modal de dotation
        $agentsAgence = User::where('structure_id', $agencyId)
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['Collectrice', 'Commercial', 'Caissier']);
            })
            ->select('id', 'name', 'phone')
            ->get();

        return view('comptabilite.coffre.index', compact(
            'soldeCoffre',
            'totalEntrees',
            'totalSorties',
            'mouvementsCoffre',
            'agentsAgence'
        ));
    }

    /**
     * Traitement des mouvements physiques de fond du Coffre-Fort
     */
    public function storeMouvementCoffre(Request $request)
    {
        $request->validate([
            'action_type' => 'required|in:vault_deposit,vault_withdrawal,agent_dotation',
            'amount'      => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
            'target_agent_id' => 'required_if:action_type,agent_dotation|nullable|exists:users,id',
        ]);

        $comptable = Auth::user();

        // Verification du solde en cas de sortie
        if (in_array($request->action_type, ['vault_withdrawal', 'agent_dotation'])) {
            $entrees = DB::table('transactions')->whereNull('account_id')->whereIn('type', ['vault_deposit', 'transfer'])->sum('amount');
            $sorties = DB::table('transactions')->whereNull('account_id')->whereIn('type', ['vault_withdrawal', 'agent_dotation'])->sum('amount');
            $soldeDisponible = $entrees - $sorties;

            if ($request->amount > $soldeDisponible) {
                return redirect()->back()->with('error', 'Opération impossible : Solde insuffisant dans le coffre-fort (' . number_format($soldeDisponible, 0, ',', ' ') . ' XAF dispo).');
            }
        }

        $refPrefix = match($request->action_type) {
            'vault_deposit'    => 'APP-',
            'vault_withdrawal' => 'BNQ-',
            'agent_dotation'   => 'DOT-',
        };

        DB::transaction(function () use ($request, $comptable, $refPrefix) {
            // Libellé personnalisé
            $desc = $request->description;
            if ($request->action_type === 'agent_dotation') {
                $targetAgent = User::find($request->target_agent_id);
                $desc = '[Dotation Caisse Agent] Pour : ' . ($targetAgent->name ?? 'Agent') . ' - ' . $request->description;
            }

            DB::table('transactions')->insert([
                'account_id'   => null, // Opération interne coffre
                'performed_by' => $comptable->id,
                'type'         => $request->action_type,
                'amount'       => $request->amount,
                'fees'         => 0,
                'reference'    => $refPrefix . strtoupper(uniqid()),
                'description'  => $desc,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Mouvement de coffre enregistré avec succès !');
    }

    /**
     * Suivi consolidé des Flux Financiers par Agence et par Zone
     */
    public function flux(Request $request)
    {
        $comptable = Auth::user();
        $agency = Structure::with('zones')->findOrFail($comptable->structure_id);

        // Filtre de période (par défaut le mois en cours)
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // 1. Synthèse par Zone de l'agence
        $zonesStats = $agency->zones->map(function ($zone) use ($startDate, $endDate) {
            // IDs des agents de cette zone
            $agentIds = User::where('zone_id', $zone->id)->pluck('id');

            // Dépôts collectés dans la zone
            $depots = DB::table('transactions')
                ->whereIn('performed_by', $agentIds)
                ->where('type', 'deposit')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('amount');

            // Retraits effectués dans la zone
            $retraits = DB::table('transactions')
                ->whereIn('performed_by', $agentIds)
                ->where('type', 'withdrawal')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('amount');

            // Nombre de collectes effectuées
            $nbTransactions = DB::table('transactions')
                ->whereIn('performed_by', $agentIds)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count();

            $zone->total_depots = $depots;
            $zone->total_retraits = $retraits;
            $zone->flux_net = $depots - $retraits;
            $zone->nb_transactions = $nbTransactions;
            $zone->nb_agents = $agentIds->count();

            return $zone;
        });

        // 2. Performance des agents par Zone (Top Collecteurs)
        $topAgents = User::where('structure_id', $agency->id)
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['Collectrice', 'Commercial']);
            })
            ->with('zone')
            ->get()
            ->map(function($agent) use ($startDate, $endDate) {
                $agent->total_collecte = DB::table('transactions')
                    ->where('performed_by', $agent->id)
                    ->where('type', 'deposit')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->sum('amount');
                return $agent;
            })
            ->sortByDesc('total_collecte')
            ->take(5);

        // 3. Totaux globaux de l'agence sur la période
        $totalAgenceDepots = $zonesStats->sum('total_depots');
        $totalAgenceRetraits = $zonesStats->sum('total_retraits');
        $totalAgenceNet = $totalAgenceDepots - $totalAgenceRetraits;

        return view('comptabilite.flux.index', compact(
            'agency',
            'zonesStats',
            'topAgents',
            'totalAgenceDepots',
            'totalAgenceRetraits',
            'totalAgenceNet',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Consultation et Gestion des Comptes Clients de l'Agence
     */
    public function clients(Request $request)
    {
        $comptable = Auth::user();
        $agencyId = $comptable->structure_id;
        $search = $request->get('search');
        $accountType = $request->get('account_type');

        $zones = Zone::where('structure_id', $agencyId)->get();

        // 🟢 CORRECTION : Eager-loading imbriqué pour charger accounts ET subAccounts
        $query = User::where('structure_id', $agencyId)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Client');
            })
            ->with(['zone', 'accounts.subAccounts']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($accountType) {
            $query->whereHas('accounts', function ($q) use ($accountType) {
                $q->where('type', $accountType);
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(15);

        // Formater la liste des sous-comptes à plat pour chaque client (AlpineJS / Modales)
        $clients->getCollection()->transform(function ($client) {
            $subAccountsList = collect();
            foreach ($client->accounts as $acc) {
                if ($acc->relationLoaded('subAccounts')) {
                    $subAccountsList = $subAccountsList->merge($acc->subAccounts);
                }
            }
            $client->sub_accounts = $subAccountsList;
            return $client;
        });

        $tontineTypes = [
            'simple'         => 'Tontine Simple',
            'scolaire'       => 'Tontine Scolaire',
            'investissement' => 'Tontine Investissement',
            'fin_annee'      => 'Tontine Fin d\'Année',
            'assurance'      => 'Tontine Assurance',
            'islamique'      => 'Tontine Islamique',
            'marchande'      => 'Tontine Marchande',
            'electromenager' => 'Tontine Électroménager',
        ];

        // Stats Globales
        $totalClientsActifs = User::where('structure_id', $agencyId)->where('status', 'active')->count();

        // 🟢 Calcul de l'épargne globale (Comptes principaux + Sous-comptes)
        $epargneMain = DB::table('accounts')
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->where('users.structure_id', $agencyId)
            ->sum('accounts.balance');

        $epargneSub = DB::table('sub_accounts')
            ->join('accounts', 'sub_accounts.account_id', '=', 'accounts.id')
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->where('users.structure_id', $agencyId)
            ->sum('sub_accounts.balance');

        $totalEpargneGlobal = $epargneMain + $epargneSub;

        return view('comptabilite.clients.index', compact(
            'clients',
            'zones',
            'tontineTypes',
            'totalClientsActifs',
            'totalEpargneGlobal',
            'search',
            'accountType'
        ));
    }

    /**
     * Enregistrement d'un Nouveau Client + Ses Tontines Initiales
     */
    public function storeClient(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|unique:users,phone',
            'email'            => 'nullable|email|unique:users,email',
            'zone_id'          => 'required|exists:zones,id',
            'tontines'         => 'required|array|min:1',
            'tontines.*'       => 'required|in:simple,scolaire,investissement,fin_annee,assurance,islamique,marchande,electromenager',
            'deposits'         => 'required|array',
            'deposits.*'       => 'required|numeric|min:1000', // Minimum 1000 XAF obligatoire
        ], [
            'deposits.*.min'   => 'Le versement initial par tontine doit être d\'au moins 1 000 XAF pour activation.',
            'tontines.required'=> 'Veuillez sélectionner au moins une tontine pour le client.',
        ]);

        $comptable = Auth::user();

        DB::beginTransaction();
        try {
            // 1. Création de l'utilisateur Client
            $client = User::create([
                'name'         => $request->name,
                'phone'        => $request->phone,
                'email'        => $request->email,
                'password'     => Hash::make($request->phone), // Mot de passe par défaut = téléphone
                'structure_id' => $comptable->structure_id,
                'zone_id'      => $request->zone_id,
                'status'       => 'active',
            ]);

            // Assignation du rôle Client
            if (method_exists($client, 'assignRole')) {
                $client->assignRole('Client');
            }

            // 2. Création des Tontines (Accounts) et Versement Initial
            foreach ($request->tontines as $index => $tontineType) {
                $depositAmount = $request->deposits[$tontineType] ?? 1000;

                $account = DB::table('accounts')->insertGetId([
                    'user_id'        => $client->id,
                    'account_number' => 'ACC-' . strtoupper(Str::random(3)) . '-' . rand(10000, 99999),
                    'type'           => $tontineType,
                    'balance'        => $depositAmount,
                    'reserve_fund'   => 1000.00,
                    'status'         => 'active',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // 🟢 AJOUT : Création automatique du sous-compte associé
                SubAccount::create([
                    'account_id'      => $account->id, // Laison au compte principal
                    'tontine_plan_id' => $request->tontine_plan_ids[$tontineType] ?? null, // Si un plan est transmis
                    'name'            => 'Sous-compte ' . ucfirst($tontineType),
                    'code'            => 'SUB-' . strtoupper(Str::random(5)),
                    'balance'         => $depositAmount,
                    'target_amount'   => 0, // Ou montant visé selon le besoin
                    'color'           => '#4F46E5',
                    'status'          => 'active',
                ]);

                // Transaction Dépôt Initial
                DB::table('transactions')->insert([
                    'account_id'   => $account,
                    'performed_by' => $comptable->id,
                    'type'         => 'deposit',
                    'amount'       => $depositAmount,
                    'fees'         => 0.00,
                    'reference'    => 'DEP-INIT-' . strtoupper(Str::random(8)),
                    'description'  => 'Dépôt initial obligatoire à la création (Tontine ' . ucfirst($tontineType) . ')',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('comptabilite.clients.show', $client->id)
                ->with('success', 'Nouveau client et ses tontines créés avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors de la création : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Fiche Détaillée d'un Client
     */
    public function showClient(Request $request, $id)
    {
        $comptable = Auth::user();

        // Récupération du client
        $client = User::where('structure_id', $comptable->structure_id)
            ->with(['zone', 'structure', 'accounts'])
            ->findOrFail($id);

        // Collectrices affectées à la zone du client
        $collectrices = User::where('zone_id', $client->zone_id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Collectrice', 'Commercial']);
            })->get();

        // Agent créateur (déduit du premier dépôt de son premier compte)
        $firstAccount = $client->accounts->first();
        $creator = null;
        if ($firstAccount) {
            $firstTx = DB::table('transactions')
                ->where('account_id', $firstAccount->id)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($firstTx) {
                $creator = User::find($firstTx->performed_by);
            }
        }

        // Filtres Transactions
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $txType = $request->get('tx_type');

        $accountIds = $client->accounts->pluck('id');

        $txQuery = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->join('users as agents', 'transactions.performed_by', '=', 'agents.id')
            ->select('transactions.*', 'accounts.account_number', 'accounts.type as account_type', 'agents.name as agent_name')
            ->whereIn('transactions.account_id', $accountIds);

        if ($startDate) {
            $txQuery->whereDate('transactions.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $txQuery->whereDate('transactions.created_at', '<=', $endDate);
        }
        if ($txType) {
            $txQuery->where('transactions.type', $txType);
        }

        $transactions = $txQuery->orderBy('transactions.created_at', 'desc')->paginate(10);

        // Achats Électroménager / Panier Boutique
        $boutiqueTransactions = DB::table('transactions')
            ->whereIn('account_id', $accountIds)
            ->where('type', 'product_payment')
            ->orderBy('created_at', 'desc')
            ->get();

        // Types de Tontines
        $tontineTypes = [
            'simple'         => 'Tontine Simple',
            'scolaire'       => 'Tontine Scolaire',
            'investissement' => 'Tontine Investissement',
            'fin_annee'      => 'Tontine Fin d\'Année',
            'assurance'      => 'Tontine Assurance',
            'islamique'      => 'Tontine Islamique',
            'marchande'      => 'Tontine Marchande',
            'electromenager' => 'Tontine Électroménager',
        ];

        return view('comptabilite.clients.show', compact(
            'client',
            'collectrices',
            'creator',
            'transactions',
            'boutiqueTransactions',
            'tontineTypes',
            'startDate',
            'endDate',
            'txType'
        ));
    }

    /**
     * Ajouter une nouvelle Tontine à un Client Existant
     */
    public function addTontine(Request $request, $clientId)
    {
        // 1. Corriger les clés de validation pour correspondre à name="tontine_plan_id"
        $request->validate([
            'tontine_plan_id' => 'required', // Ajoutez la règle adaptée (ex: exists:tontine_plans,id ou String)
            'initial_deposit' => 'required|numeric|min:1000',
        ], [
            'initial_deposit.min' => 'Le versement initial doit être d\'au moins 1 000 XAF.',
            'tontine_plan_id.required' => 'Veuillez sélectionner un plan de tontine.',
        ]);

        $client = User::findOrFail($clientId);
        $comptable = Auth::user();

        // 2. Récupérer le compte principal du client
        $account = Account::where('user_id', $client->id)->first();

        if (!$account) {
            return redirect()->back()->with('error', 'Le client ne possède aucun compte principal actif.');
        }

        // 3. Vérifier si le client possède déjà cette tontine
        $exists = DB::table('sub_accounts')
            ->where('account_id', $account->id)
            ->where('tontine_plan_id', $request->tontine_plan_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Le client possède déjà ce type de tontine.');
        }

        DB::beginTransaction();
        try {
            // 4. Créer le sous-compte dans 'sub_accounts'
            $subAccountId = DB::table('sub_accounts')->insertGetId([
                'account_id'         => $account->id,
                'code'               => 'SUB-' . strtoupper(Str::random(3)) . '-' . rand(100, 999),
                'name'               => ucfirst($request->tontine_plan_id),
                'tontine_plan_id'    => $request->tontine_plan_id,
                'balance'            => $request->initial_deposit,
                'status'             => 'active',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // 5. Enregistrer la transaction
            DB::table('transactions')->insert([
                'account_id'     => $account->id,
                'sub_account_id' => $subAccountId,
                'performed_by'   => $comptable->id,
                'type'           => 'deposit',
                'amount'         => $request->initial_deposit,
                'fees'           => 0.00,
                'reference'      => 'DEP-NEW-' . strtoupper(Str::random(8)),
                'description'    => 'Ouverture nouvelle tontine : ' . ucfirst($request->tontine_plan_id),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Nouvelle tontine ajoutée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Changer l'état global du compte Client (Activer, Bloquer, Suspendre, Geler, Clôturer)
     */
    public function updateClientStatus(Request $request, $clientId)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,blocked,frozen,closed',
        ]);

        $client = User::findOrFail($clientId);

        if ($request->status === 'closed') {
            // Clôture : Suspend le client et ferme toutes ses tontines
            $client->update(['status' => 'suspended']);
            DB::table('accounts')->where('user_id', $client->id)->update(['status' => 'closed', 'updated_at' => now()]);
            $msg = "Compte client et tontines associées clôturés avec succès.";
        } elseif ($request->status === 'frozen') {
            // Gelé : Tontines gelées
            DB::table('accounts')->where('user_id', $client->id)->update(['status' => 'frozen', 'updated_at' => now()]);
            $msg = "Tontines du client gelées avec succès.";
        } else {
            // Actif / Suspendu / Bloqué
            $client->update(['status' => $request->status === 'active' ? 'active' : 'suspended']);
            $accountStatus = $request->status === 'active' ? 'active' : ($request->status === 'blocked' ? 'suspended' : 'suspended');
            DB::table('accounts')->where('user_id', $client->id)->update(['status' => $accountStatus, 'updated_at' => now()]);
            $msg = "Statut du client mis à jour vers : " . ucfirst($request->status);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function stockVente(Request $request)
    {
        $user = Auth::user();
        $agencyColumn = Schema::hasColumn('users', 'structure_id') ? 'structure_id' : 'agency_id';
        $agencyId = $user->{$agencyColumn};

        // 1. Statistiques Globales des Stocks
        $articlesQuery = Product::where('agency_id', $agencyId);

        $totalStockItems = (clone $articlesQuery)->sum('stock');

        // Valorisation du stock (Prix d'achat vs Prix Vente)
        $stockValueCost = (clone $articlesQuery)->get()->sum(fn($a) => $a->stock * ($a->purchase_price ?? 0));
        $stockValueSelling = (clone $articlesQuery)->get()->sum(fn($a) => $a->stock * $a->cash_price);

        // 2. Statistiques des Commandes / Contrats Articles
        $ordersQuery = Order::where('agency_id', $agencyId);

        $totalSalesCash = (clone $ordersQuery)->where('payment_type', 'cash')->sum('total_amount');
        $totalSalesInstallment = (clone $ordersQuery)->where('payment_type', 'installment')->sum('total_amount');
        $totalCollectedInstallments = (clone $ordersQuery)->where('payment_type', 'installment')->sum('paid_amount');

        // 3. Filtrage de la liste des articles
        $articles = (clone $articlesQuery)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->when($request->filled('stock_status'), function ($q) use ($request) {
                if ($request->stock_status === 'low') {
                    $q->whereColumn('stock', '<=', 'min_stock_threshold');
                } elseif ($request->stock_status === 'out') {
                    $q->where('stock', '<=', 0);
                }
            })
            ->latest()
            ->paginate(15, ['*'], 'articles_page')
            ->withQueryString();

        // 4. Dernières Ventes & Contrats pour Audit
        $recentOrders = (clone $ordersQuery)
            ->with(['user', 'article'])
            ->latest()
            ->take(10)
            ->get();

        return view('comptabilite.boutique.index', compact(
            'articles',
            'recentOrders',
            'totalStockItems',
            'stockValueCost',
            'stockValueSelling',
            'totalSalesCash',
            'totalSalesInstallment',
            'totalCollectedInstallments'
        ));
    }

    public function echeanciers()
    {
        return view('comptabilite.echeanciers.index');
    }

    public function rapports()
    {
        return view('comptabilite.rapports.index');
    }
}
