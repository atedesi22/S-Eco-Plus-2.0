<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Product;
use App\Models\Structure;
use App\Models\Tontine_plan;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    //
    // 1. Tableau de bord SuperAdmin (Statistiques globales du système)
    public function dashboard(Request $request)
    {
        $totalClients = User::role('Client')->count();
        $totalCommercials = User::role('Commercial')->count();
        $totalPlans = Tontine_plan::count();

        // Correction Spatie : Récupérer le personnel (exclure le rôle Client)
        $staffMembers = User::withoutRole('Client')->latest()->take(5)->get();

        // Récupérer tout le staff existant pour pouvoir les affecter comme supérieur hiérarchique dans le formulaire
        $superiors = User::withoutRole('Client')->orderBy('name')->get();

        // Optionnel : Statistiques pour tes deux premières cartes
        $totalRegions = Structure::where('type', 'regional_direction')->count();
        $totalAgencies = Structure::where('type', 'agency')->count();


        $period = $request->input('period', 'month'); // Période par défaut


        $startDate = Carbon::now();

        // Définition de la plage de date selon le filtre
        switch ($period) {
            case 'day':
                $startDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'quarter': // Trimestre
                $startDate = Carbon::now()->startOfQuarter();
                break;
            case 'semester': // Semestre (6 mois)
                $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                break;
        }

        // 1. Nombre de comptes créés (Rôle client ou filtre générique)
        $accountsCount = User::where('created_at', '>=', $startDate)->count();

        // 2. Volume Total des Transactions
        $totalTransactions = Transaction::where('created_at', '>=', $startDate)->sum('amount');

        // 3. Gains (Frais d'adhésion, intérêts perçus, marges boutiques, frais de dossiers...)
        $gains = Transaction::where('created_at', '>=', $startDate)
            ->whereIn('type', ['frais_dossier', 'interets', 'commission', 'vente_cash'])
            ->sum('amount');

        // 4. Pertes (Crédits en défaut, annulations, charges d'exploitation...)
        $pertes = Transaction::where('created_at', '>=', $startDate)
            ->whereIn('type', ['perte_credit', 'charge_exploitation', 'remboursement'])
            ->sum('amount');

        // Préparation des données chronologiques pour les graphiques (Ex: Agrégation par date)
        $chartData = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw("SUM(CASE WHEN type IN ('frais_dossier', 'interets', 'commission', 'vente_cash') THEN amount ELSE 0 END) as gains"),
                DB::raw("SUM(CASE WHEN type IN ('perte_credit', 'charge_exploitation', 'remboursement') THEN amount ELSE 0 END) as pertes")
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();


        return view('admin.dashboard', compact(
            'totalClients',
            'totalCommercials',
            'totalPlans',
            'staffMembers',
            'superiors',
            'totalRegions',
            'totalAgencies',
            'accountsCount',
            'totalTransactions',
            'gains',
            'pertes',
            'period',
            'chartData'
        ));
    }

    // 2. Catalogue des Tontines : Liste & Création
    public function tontinePlansIndex()
    {
        $plans = Tontine_plan::all();
        return view('admin.tontines.index', compact('plans'));
    }

    public function storeTontinePlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:tontine_plans,name|max:100',
            'default_color' => 'required|string|max:30', // emerald, indigo, amber...
            'description' => 'nullable|string'
        ]);

        Tontine_plan::create([
            'name' => $request->name,
            'default_color' => $request->default_color,
            'description' => $request->description,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Nouveau type de tontine ajouté au catalogue central !');
    }

    // 3. Gestion du Staff : Création d'un Directeur Régional ou d'Agence
    public function createStaffUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'parent_id' => 'nullable|exists:users,id', // Le supérieur hiérarchique
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'parent_id' => $request->parent_id,
        ]);

        // Attribuer le rôle Spatie
        $user->assignRole($request->role);

        return redirect()->back()->with('success', "Le compte de {$user->name} ({$request->role}) a été configuré avec succès !");
    }

    // 4. Liste complète du Staff pour édition / révocation
    public function staffIndex()
    {
        // Récupérer tout le personnel avec son rôle et sa structure
        $staff = User::withoutRole('Client')->with(['structure', 'roles'])->latest()->get();
        // Liste des structures pour une éventuelle réaffectation rapide
        $structures = \App\Models\Structure::all();
        // Liste des supérieurs potentiels
        $superiors = User::withoutRole('Client')->get();

        return view('admin.staff.index', compact('staff', 'structures', 'superiors'));
    }

    // 5. Suspension ou Révocation d'un compte staff
    public function toggleStaffStatus($id)
    {
        $user = User::findOrFail($id);

        // Sécurité : Le SuperAdmin ne peut pas se désactiver lui-même
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Action interdite : Vous ne pouvez pas suspendre votre propre compte.');
        }

        // Si tu as une colonne is_active dans ta table users
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', "Le statut du compte de {$user->name} a été mis à jour.");
    }

    // 6. VUE CLIENTS GLOBALE
    public function clientsIndex()
    {
        $clients = User::role('Client')->with('structure')->latest()->paginate(15);
        return view('admin.modules.clients', compact('clients'));
    }

    // 7. BOUTIQUE CENTRALISÉE
    public function shopIndex()
    {
        // Récupérer les articles physiques destinés à la vente / octroi de crédit bail
        // $products = Product::latest()->get();
        // Récupérer tous les produits
        $products = Product::all();
        return view('admin.modules.shop', compact('products'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price_cash' => 'required|numeric|min:0',
            'selling_price_installment' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'primary_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery' => 'nullable|array|max:3',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // 1. Génération automatique de la référence unique
        $reference = 'PRD-' . date('Y') . '-' . strtoupper(Str::random(6));

        // 2. Traitement de l'image principale
        $primaryPath = null;
        if ($request->hasFile('primary_image')) {
            $primaryPath = $request->file('primary_image')->store('products/primary', 'public');
        }

        // 3. Traitement des images secondaires (Max 3)
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = $file->store('products/gallery', 'public');
            }
        }

        // 4. Sauvegarde en Base de Données
        Product::create([
            'reference' => $reference,
            'name' => $request->name,
            'purchase_price' => $request->purchase_price,
            'selling_price_cash' => $request->selling_price_cash,
            'selling_price_installment' => $request->selling_price_installment,
            'stock' => $request->stock,
            'primary_image' => $primaryPath,
            'gallery_images' => $galleryPaths, // Casté automatiquement en JSON
        ]);

        return redirect()->back()->with('success', 'Produit référencé dans le stock central.');
    }

    // 8. TOUTES LES TRANSACTIONS & ETAT FINANCIER GLOBAL
    public function financesIndex()
    {
        $transactions = Transaction::with(['performedBy', 'account.user'])->latest()->paginate(20);

        // Calculs globaux analytiques
        $totalDeposits = Transaction::where('type', 'deposit')->sum('amount');
        $totalWithdrawals = Transaction::where('type', 'withdrawal')->sum('amount');
        $totalFeesCollected = Transaction::sum('fees'); // Gains générés par les paliers de 500 XAF

        // Trésorerie nette du réseau S Eco Plus
        $netVault = $totalDeposits - $totalWithdrawals;

        return view('admin.modules.finances', compact('transactions', 'totalDeposits', 'totalWithdrawals', 'totalFeesCollected', 'netVault'));
    }

    // Action d'urgence : Forcer un rééquilibrage de compte
    public function forceTransaction(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:100',
            'description' => 'required|string|max:255'
        ]);

        // Implémenter ici ton calcul des frais (ex: 500 XAF par palier de 25 000 XAF pour retrait)
        $fee = 0;
        if ($request->type === 'withdrawal') {
            $fee = ceil($request->amount / 25000) * 500;
        }

        // Création de l'écriture d'audit forcée par le SuperAdmin
        Transaction::create([
            'account_id' => $request->account_id,
            'type' => $request->type,
            'amount' => $request->amount,
            'fees' => $fees,
            'performed_by' => auth()->id(),
            'description' => '[SUPERADMIN FORCE] ' . $request->description,
        ]);

        // Mettre à jour le solde du compte cible
        $account = Account::find($request->account_id);
        if ($request->type === 'deposit') {
            $account->increment('balance', $request->amount);
        } else {
            $account->decrement('balance', ($request->amount + $fees));
        }

        return redirect()->back()->with('success', 'Opération financière exceptionnelle exécutée.');
    }

    // 9. CENTRALISATION DES RAPPORTS DE PERFORMANCE
    public function reportsIndex()
    {
        // Analyser l'activité de collecte des commerciaux et collectrices
        $staffPerformances = User::withoutRole('Client')
            ->withCount(['transactionsAsValidator' => function($q) {
                $q->where('created_at', '>=', now()->startOfMonth());
            }])
            ->get();

        return view('admin.modules.reports', compact('staffPerformances'));
    }

    //10. RESET PASSWORD UTILISATEUR A 000
    public function resetUserPassword($id)
    {
        $user = User::findOrFail($id);
        $user->resetPasswordToDefault();

        return redirect()->back()->with('success', "Le mot de passe de l'agent a été réinitialisé à '0000'.");
    }

    public function showProfile($id)
    {
        // Chargement complet du profil avec son historique managérial et financier
        $user = User::with([
            'objectives',
            'sanctions.objective',
        ])->findOrFail($id);

        // Si tu as un modèle Transaction lié à l'utilisateur, on récupère les 10 dernières
        // Sinon, on initialise une collection vide pour éviter les erreurs Blade
        $transactions = class_exists(\App\Models\Transaction::class)
            ? \App\Models\Transaction::where('performed_by', $id)->orderBy('created_at', 'desc')->take(10)->get()
            : collect();

        return view('admin.modules.user-profile', compact('user', 'transactions'));
    }
}


