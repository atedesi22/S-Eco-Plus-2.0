<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Agency;
use App\Models\CashDeposit;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\Structure;
use App\Models\SubAccount;
use App\Models\Tontine_plan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CommercialDashboardController extends Controller
{
    //
    public function index()
    {
        $commercialId = Auth::id();

        // 1. Calculs des KPIs du Commercial
        $todayCollected = Transaction::where('performed_by', $commercialId)
            ->where('type', 'deposit')
            ->whereDate('created_at', now()->today())
            ->sum('amount');

        $monthlyCollected = Transaction::where('performed_by', $commercialId)
            ->where('type', 'deposit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $kpis = [
            'today_collected'      => $todayCollected,
            'monthly_collected'    => $monthlyCollected,
            'monthly_target'       => 1000000, // Objectif configurable par agent
            'pending_cash'         => $todayCollected, // Total en possession non encore versé en caisse
            'new_clients_count'    => User::where('collector_id', $commercialId)->whereMonth('created_at', now()->month)->count(),
            'estimated_commission' => round($monthlyCollected * 0.05), // Exemple: 5% de commission sur cotisations
        ];

        // 2. Dernières collectes effectuées
        $recentCollects = Transaction::where('performed_by', $commercialId)
            ->with(['account.user', 'subAccount'])
            ->latest()
            ->take(6)
            ->get();

        // 3. Liste des clients rattachés à ce commercial
        $myClients = User::where('collector_id', $commercialId)
            ->get(['id', 'name', 'phone']);

        return view('commercial.dashboard', compact('kpis', 'recentCollects', 'myClients'));
    }

    // Gestion des Clients par le Commercial
    public function clientIndex(Request $request)
    {
        $search = $request->input('search');

        $clients = User::where(function($query) {
                $query->where('created_by', Auth::id());
                    //   ->orWhere('collector_id', Auth::id());
            })
            ->with(['accounts.subAccounts', 'collector'])
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);

        return view('commercial.clients.index', compact('clients', 'search'));
    }

    public function clientCreate()
    {
        // $commercial = Auth::user();

        // $agencies = $commercial->agency->id;
        $commercial = Auth::user()->load('structure');
        // return $commercial;
        // $agencies = Agency::all();
        $zones = Zone::all();
        $structures = Structure::all();

        $collectors = User::whereHas('roles', function($q) {
            $q->where('name', 'Collectrice');
        })->get();

        $tontineTypes = Tontine_plan::all();

        return view('commercial.clients.create', compact('commercial', 'zones', 'structures', 'collectors', 'tontineTypes'));
    }

    public function clientStore(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'nullable|email|unique:users,email',
            'phone'            => 'required|string|max:20|unique:users,phone',
            'password'         => 'required|string|min:6',
            // 'agency_id'        => 'nullable|exists:agencies,id',
            'zone_id'          => 'nullable|exists:zones,id',
            'structure_id'     => 'nullable|exists:structures,id',
            'collector_id'     => 'nullable|exists:users,id',
            'profile_photo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'           => 'required|string|in:active,inactive,pending',

            // Validation de la tontine initiale (sous-compte)
            'tontine_type_id' => 'required|exists:tontine_plans,id',
            'tontine_name'     => 'nullable|string|max:255',
            'initial_deposit' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request, $validated) {

            // 1. Upload photo de profil si présente
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            $authUser = Auth::user();

            // 2. Création de l'utilisateur (Client)
            $client = User::create([
                'name'          => $validated['name'],
                'email'         => $validated['email'] ?? null,
                'phone'         => $validated['phone'],
                'password'      => Hash::make($validated['password']),
                // 'agency_id'     => $validated['agency_id'] ?? $authUser->agency_id,
                'zone_id'       => $validated['zone_id'] ?? $authUser->zone_id,
                'created_by'    => Auth::id(), // Commercial créateur
                'structure_id'  => $validated['structure_id'] ?? $authUser->structure_id,
                'collector_id'  => $validated['collector_id'] ?? null, // Collectrice attribuée
                'mfa_enabled'   => false,
                'status'        => $validated['status'] ?? 'active',
                'profile_photo' => $photoPath,
            ]);

            $client->assignRole('Client');

            $initialAmount = $validated['initial_deposit'];
            $tontineTypeId = $validated['tontine_type_id'];

            // 3. Création automatique du compte principal d'épargne (Account)
            $account = Account::create([
                'user_id'        => $client->id,
                'account_number' => 'ACC-' . strtoupper(uniqid()),
                'balance'        => $initialAmount,
                'status'         => 'active',
            ]);

            // 4. Création du Sous-compte / Tontine (SubAccount) si une tontine est spécifiée
            if (!empty($validated['tontine_name'])) {
                SubAccount::create([
                    'account_id'   => $account->id,
                    'tontine_plan_id' => $tontineTypeId,
                    'name'         => $validated['tontine_name'], // Ex: "Tontine Journalière 1000 XAF"
                    'code'            => 'SUB-' . strtoupper(Str::random(5)),
                    // 'daily_amount' => $validated['daily_amount'] ?? 0,
                    'balance'      => $initialAmount,
                    'status'       => 'active',
                ]);
            }

            return redirect()->route('commercial.clients.index')
                ->with('success', "Le compte client et la tontine de {$client->name} ont été créés avec succès.");
        });
    }

    /**
     * Fiche Détaillée du Client en Lecture Seule.
     */
    public function clientShow($id)
    {
        $client = User::with(['accounts.subAccounts', 'collector', 'managedClients', 'agency', 'zone'])
            ->where(function($q) {
                $q->where('created_by', Auth::id())
                ->orWhere('collector_id', Auth::id());
            })
            ->findOrFail($id);

            // Récupère les 10 dernières transactions du client sur ses comptes
            $accountIds = $client->accounts->pluck('id');

            $recentTransactions = \App\Models\Transaction::whereIn('account_id', $accountIds)
            ->with('subAccount')
            ->latest()
            ->take(10)
            ->get();

        return view('commercial.clients.show', compact('client', 'recentTransactions'));
    }

    /**
     * Liste des prospects du commercial connecté.
     */
    public function prospectIndex(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $prospects = Prospect::where('commercial_id', Auth::id())
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('activity_sector', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(12);

        // Compteurs pour les KPIs rapides
        $stats = [
            'total'       => Prospect::where('commercial_id', Auth::id())->count(),
            'nouveaux'    => Prospect::where('commercial_id', Auth::id())->where('status', 'nouveau')->count(),
            'en_cours'    => Prospect::where('commercial_id', Auth::id())->where('status', 'en_discussion')->count(),
            'convertis'   => Prospect::where('commercial_id', Auth::id())->where('status', 'converti')->count(),
        ];

        return view('commercial.prospects.index', compact('prospects', 'stats', 'search', 'status'));
    }

    /**
     * Formulaire d'ajout de prospect.
     */
    public function prospectCreate()
    {
        $zones = Zone::all();
        return view('commercial.prospects.create', compact('zones'));
    }

    /**
     * Enregistrement d'un nouveau prospect terrain.
     */
    public function prospectStore(Request $request)
    {
        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'activity_sector'  => 'nullable|string|max:255',
            'location'         => 'nullable|string|max:255',
            'interest_type'    => 'required|in:tontine,epargne,article,credit',
            'estimated_budget' => 'nullable|numeric|min:0',
            'zone_id'          => 'nullable|exists:zones,id',
            'next_contact_at'  => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $user = Auth::user();

        Prospect::create([
            'commercial_id'    => $user->id,
            'agency_id'        => $user->agency_id,
            'zone_id'          => $validated['zone_id'] ?? $user->zone_id,
            'full_name'        => $validated['full_name'],
            'phone'            => $validated['phone'],
            'activity_sector'  => $validated['activity_sector'],
            'location'         => $validated['location'],
            'interest_type'    => $validated['interest_type'],
            'estimated_budget' => $validated['estimated_budget'] ?? 0,
            'status'           => 'nouveau',
            'next_contact_at'  => $validated['next_contact_at'],
            'notes'            => $validated['notes'],
        ]);

        return redirect()->route('commercial.prospects.index')
            ->with('success', "Le prospect {$validated['full_name']} a été enregistré avec succès.");
    }

    /**
     * Mise à jour du statut / des notes de relance.
     */
    public function prospectUpdate(Request $request, $id)
    {
        $prospect = Prospect::where('commercial_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'status'          => 'required|in:nouveau,en_discussion,converti,abandonne',
            'next_contact_at' => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        $prospect->update($validated);

        return back()->with('success', "Informations du prospect mises à jour.");
    }

    /**
     * Catalogue des offres de tontine & historique des souscriptions faites par le commercial.
     */
    public function tontineIndex(Request $request)
    {
        $search = $request->input('search');

        // 1. Offres / Types de tontines disponibles au catalogue
        $tontineTypes = Tontine_plan::where('is_active', 'active')->get();

        // 2. Clients du portefeuille commercial pour la modal / sélection
        $clients = User::role('client')
            ->where(function($q) {
                $q->where('created_by', Auth::id())
                  ->orWhere('collector_id', Auth::id());
            })
            ->get();

        // 3. Historique des souscriptions (SubAccounts) récentes initiées par ce commercial
        $subscriptions = SubAccount::whereHas('account.user', function($q) use ($search) {
                $q->where('created_by', Auth::id());
                if ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                }
            })
            ->with(['account.user', 'plan'])
            ->latest()
            ->paginate(10);

        return view('commercial.tontines.index', compact('tontineTypes', 'clients', 'subscriptions', 'search'));
    }

    /**
     * Faire souscrire un client existant à une nouvelle tontine
     */
    public function tontineStore(Request $request)
    {
        $validated = $request->validate([
            'user_id'         => 'required|exists:users,id',
            'tontine_type_id' => 'required|exists:tontine_plans,id',
            'custom_name'     => 'nullable|string|max:255',
            'daily_amount'    => 'required|numeric|min:100',
        ]);

        return DB::transaction(function () use ($validated) {
            $client = User::findOrFail($validated['user_id']);

            // Récupère ou crée le compte principal d'épargne du client
            $account = Account::firstOrCreate(
                ['user_id' => $client->id],
                [
                    'account_number' => 'ACC-' . strtoupper(uniqid()),
                    'balance'        => 0,
                    'status'         => 'active',
                ]
            );

            $tontineType = Tontine_plan::findOrFail($validated['tontine_type_id']);

            // Création du nouveau sous-compte (Tontine)
            $subAccount = SubAccount::create([
                'account_id'      => $account->id,
                'tontine_type_id' => $tontineType->id,
                'name'            => $validated['custom_name'] ?? $tontineType->name,
                'daily_amount'    => $validated['daily_amount'],
                'balance'         => 0,
                'status'          => 'active',
            ]);

            return redirect()->route('commercial.tontines.index')
                ->with('success', "Souscription réussie ! Le sous-compte '{$subAccount->name}' a été attribué à {$client->name}.");
        });
    }

    /**
     * Historique des bordereaux de versement de l'agent.
     */
    public function versementIndex(Request $request)
    {
        $user = Auth::user();

        $versements = CashDeposit::where('commercial_id', $user->id)
            ->latest()
            ->paginate(12);

        // Synthèse rapide pour l'agent
        $stats = [
            'total_verse' => CashDeposit::where('commercial_id', $user->id)->where('status', 'approved')->sum('amount'),
            'en_attente'  => CashDeposit::where('commercial_id', $user->id)->where('status', 'pending')->sum('amount'),
            'nb_pending'  => CashDeposit::where('commercial_id', $user->id)->where('status', 'pending')->count(),
        ];

        return view('commercial.versements.index', compact('versements', 'stats'));
    }

    /**
     * Soumission d'un nouveau versement à la caisse.
     */
    public function versementStore(Request $request)
    {
        $validated = $request->validate([
            'amount'        => 'required|numeric|min:500',
            'deposit_type'  => 'required|in:frais_dossier,vente_boutique,collecte_globale,autre',
            'receipt_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
            'notes'         => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        $photoPath = null;
        if ($request->hasFile('receipt_photo')) {
            $photoPath = $request->file('receipt_photo')->store('versements-recus', 'public');
        }

        CashDeposit::create([
            'reference_code' => 'VER-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'commercial_id' => $user->id,
            'agency_id'     => $user->structure_id,
            'amount'        => $validated['amount'],
            'deposit_type'  => $validated['deposit_type'],
            'receipt_photo' => $photoPath,
            'notes'         => $validated['notes'],
            'status'        => 'pending',
        ]);

        return redirect()->route('commercial.versements.index')
            ->with('success', 'Bordereau de versement soumis avec succès. En attente de validation par la caisse.');
    }

    public function articles(Request $request)
    {
        $search = $request->input('search');

        $products = Product::where('is_available', true)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('reference', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12);

        // Récupère les clients rattachés au commercial pour le formulaire d'accord rapide
        $clients = User::role('client')
            ->where(function($q) {
                $q->where('created_by', auth()->id())
                  ->orWhere('collector_id', auth()->id());
            })
            ->get();

        return view('commercial.commandes.articles', compact('products', 'clients', 'search'));
    }

    /**
     * Liste des commandes du portefeuille commercial avec barre d'avancement (60%).
     */
    public function commandeIndex(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $orders = Order::with(['client', 'product', 'payments'])
            ->where(function($q) {
                $q->where('collector_id', Auth::id())
                  ->orWhereHas('client', fn($c) => $c->where('created_by', Auth::id()));
            })
            ->when($search, function($query, $search) {
                $query->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"));
            })
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(10);

        return view('commercial.commandes.index', compact('orders', 'search', 'status'));
    }

    /**
     * Enregistrement de la commande avec le protocole d'accord signé.
     */
    public function commandeStore(Request $request)
    {
        $validated = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'product_id'       => 'required|exists:products,id',
            'payment_type'     => 'required|in:cash,installment',
            'client_signature' => 'required|string',
            'agent_signature'  => 'required|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $client = User::findOrFail($validated['user_id']);
            $product = Product::findOrFail($validated['product_id']);

            $totalAmount = ($validated['payment_type'] === 'cash')
                ? $product->selling_price_cash
                : $product->selling_price_installment;

            $threshold60 = (int) ceil($totalAmount * 0.60);

            // 1. Récupération ou création du Compte Principal du client
            $account = Account::firstOrCreate(
                ['user_id' => $client->id],
                [
                    'account_number' => 'ACC-' . strtoupper(uniqid()),
                    'balance'        => 0,
                    'status'         => 'active',
                ]
            );

            // 2. Récupération du plan Tontine Électroménager
            $tontinePlan = Tontine_plan::where('slug', 'electromenager')
                ->orWhere('name', 'like', '%Électroménager%')
                ->first();

            // 3. Création automatique du Sous-Compte dédié à cet achat
            $subAccount = SubAccount::create([
                'account_id'      => $account->id,
                'tontine_plan_id' => $tontinePlan ? $tontinePlan->id : null,
                'code'            => 'SUB-' . strtoupper(Str::random(5)),
                'name'            => 'Tontine Article - ' . $product->name,
                'daily_amount'    => 0, // Librement alimenté lors des tournées
                'balance'         => 0,
                'status'          => 'active',
            ]);

            // 4. Création de la Commande
            $order = Order::create([
                'order_number'        => 'CMD-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'user_id'             => $client->id,
                'product_id'          => $product->id,
                'collector_id'        => Auth::id(),
                'payment_type'        => $validated['payment_type'],
                'total_amount'        => $totalAmount,
                'paid_amount'         => 0,
                'threshold_60_amount' => $threshold60,
                'status'              => 'in_progress',
                'client_signature'   => $validated['client_signature'],
                'agent_signature'    => $validated['agent_signature'],
                'signed_at'           => now(),
                'protocol_terms'      => "Protocole valant souscription au produit {$product->name}. Rattaché au sous-compte tontine N°{$subAccount->id}.",
            ]);

            return redirect()->route('commercial.commandes.index')
                ->with('success', "Commande {$order->order_number} créée ! Le sous-compte '{$subAccount->name}' a été automatiquement ouvert pour le client.");
        });
    }

    /**
     * Fiche détaillée de la commande (Protocole imprimable, barres de progression, etc.)
     */
    public function commandeShow($id)
    {
        // 1. Récupération stricte de la commande unique avec eager loading
        $order = Order::with(['client', 'product', 'payments.collector'])->findOrFail($id);

        // 2. Recherche propre du sous-compte tontine rattaché au client et au produit
        $subAccount = SubAccount::whereHas('account', function ($query) use ($order) {
                $query->where('user_id', $order->user_id);
            })
            ->where('name', 'like', "%{$order->product->name}%")
            ->first();

        return view('commercial.commandes.show', compact('order', 'subAccount'));
    }


    public function recordPayment(Order $order, int $amount, int $collectorId)
    {
        DB::transaction(function () use ($order, $amount, $collectorId) {
            $payment = OrderPayment::create([
                'order_id'     => $order->id,
                'collected_by' => $collectorId,
                'amount'       => $amount,
            ]);

            $order->paid_amount += $amount;
            $order->last_payment_at = now();

            // Alerte automatique : Passage du statut si franchissement du seuil 60%
            if ($order->paid_amount >= $order->threshold_60_amount && $order->status === 'in_progress') {
                $order->status = 'eligible_for_delivery';
            }

            if ($order->paid_amount >= $order->total_amount) {
                $order->status = 'completed';
            }

            $order->save();

            // 3. Créditation automatique du Sous-Compte Tontine Électroménager du Client
            $account = Account::where('order_id', $order->id)
                ->where('type', 'tontine_electromenager')
                ->first();

            if ($account) {
                $account->increment('balance', $amount);
            }

            return $payment;
        });
    }
}
