<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // 4. 10 Dernières Transactions au niveau de l'agence (AVEC LES JOINTURES CORRECTES)
        $recentTransactions = DB::table('transactions')
            // Jointure pour retrouver l'agent/utilisateur qui a effectué l'action
            ->leftJoin('users as agent', 'transactions.performed_by', '=', 'agent.id')
            // Jointure pour retrouver le client via son compte (ou directement via account_id selon ton model)
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

        // 5. Charger la liste des clients de l'agence avec calcul de solde
        $clientsAgence = User::role('Client')
            ->where('structure_id', $agency->id)
            ->select('id', 'name', 'phone')
            ->get()
            ->map(function ($client) {
                // Note : On utilise account_id ou performed_by selon où est stocké l'ID du client
                $depots = DB::table('transactions')
                    ->where('account_id', $client->id)
                    ->where('type', 'deposit')
                    ->sum('amount');

                $retraits = DB::table('transactions')
                    ->where('account_id', $client->id)
                    ->where('type', 'withdrawal')
                    ->sum('amount');

                $client->balance = $depots - $retraits;
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
    $request->validate([
        'user_id' => 'required|exists:users,id', // ID du Client sélectionné
        'type'    => 'required|in:deposit,withdrawal',
        'amount'  => 'required|numeric|min:100',
    ]);

    $comptable = Auth::user();
    $typeLabel = $request->type === 'deposit' ? 'Dépôt' : 'Retrait';

    // 1. Récupérer le compte (Account) associé au client
    $account = DB::table('accounts')->where('user_id', $request->user_id)->first();

    return $account;

    // Sécurité au cas où le client n'a pas encore de compte créé dans la table `accounts`
    if (!$account) {
        return redirect()->back()->with('error', 'Erreur : Aucun compte bancaire/épargne associé à ce client.');
    }

    // 2. Si c'est un RETRAIT, vérification du solde sur la table `transactions` avec `account_id`
    if ($request->type === 'withdrawal') {
        $depots = DB::table('transactions')
            ->where('account_id', $account->id)
            ->where('type', 'deposit')
            ->sum('amount');

        $retraits = DB::table('transactions')
            ->where('account_id', $account->id)
            ->where('type', 'withdrawal')
            ->sum('amount');

        $soldeDispo = $depots - $retraits;

        if ($request->amount > $soldeDispo) {
            return redirect()->back()->with('error', 'Retrait impossible : Solde insuffisant (' . number_format($soldeDispo, 0, ',', ' ') . ' XAF disponible).');
        }
    }

    $prefix = $request->type === 'deposit' ? 'DEP-' : 'RET-';

    // 3. Insertion avec le vrai `account_id` (ID de la table `accounts`)
    DB::table('transactions')->insert([
        'account_id'   => $account->id,       // <-- Utilisation de $account->id au lieu de $request->user_id
        'performed_by' => $comptable->id,
        'type'         => $request->type,
        'amount'       => $request->amount,
        'fees'         => 0,
        'reference'    => $prefix . strtoupper(uniqid()),
        'description'  => $typeLabel . ' Express au Guichet',
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    return redirect()->back()->with('success', $typeLabel . ' de ' . number_format($request->amount, 0, ',', ' ') . ' XAF effectué avec succès !');
}

    /**
     * Traitement de la transaction de Retrait Express au Guichet
     */
    // public function storeRetrait(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'amount'  => 'required|numeric|min:100',
    //     ]);

    //     $comptable = Auth::user();

    //     // Calcul du solde actuel du client
    //     $depots = DB::table('transactions')->where('user_id', $request->user_id)->where('type', 'deposit')->sum('amount');
    //     $retraits = DB::table('transactions')->where('user_id', $request->user_id)->where('type', 'withdrawal')->sum('amount');
    //     $soldeDispo = $depots - $retraits;

    //     // Vérification de sécurité du solde
    //     if ($request->amount > $soldeDispo) {
    //         return redirect()->back()->with('error', 'Retrait impossible : Le solde du client est insuffisant (' . number_format($soldeDispo, 0, ',', ' ') . ' XAF disponible).');
    //     }

    //     // Enregistrement du retrait en BDD
    //     DB::table('transactions')->insert([
    //         'user_id'      => $request->user_id,
    //         'performed_by' => $comptable->id,
    //         'type'         => 'withdrawal',
    //         'amount'       => $request->amount,
    //         'created_at'   => now(),
    //         'updated_at'   => now(),
    //     ]);

    //     return redirect()->back()->with('success', 'Retrait de ' . number_format($request->amount, 0, ',', ' ') . ' XAF effectué avec succès !');
    // }

    public function ecritures()
    {
        return view('comptabilite.ecritures.index');
    }

    public function coffre()
    {
        return view('comptabilite.coffre.index');
    }

    public function flux()
    {
        return view('comptabilite.flux.index');
    }

    public function clients()
    {
        return view('comptabilite.clients.index');
    }

    public function boutique()
    {
        return view('comptabilite.boutique.index');
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
