<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\SubAccount;
use App\Models\Tontine_plan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    //
    public function index()
    {
        $client = Auth::user();
        // Récupère le compte actif avec ses sous-comptes et ses 5 dernières transactions
        $account = $client->accounts()
            ->where('status', 'active')
            ->with(['subAccounts', 'transactions' => function($query) {
                $query->latest()->take(5); // Récupère uniquement les 5 derniers mouvements
            }])
            ->first();

            // AJOUT COMPLÉMENTAIRE ICI : Charger les plans de tontine pour le formulaire modal
        $tontinePlans = Tontine_plan::where('is_active', true)->get();

        // Sécurité : Si le client n'a aucun compte actif, on évite le crash
        if (!$account) {
            return redirect()->route('welcome')->with('error', 'Aucun compte épargne actif trouvé.');
        }

        // On extrait les variables pour correspondre à notre vue Blade
        $subAccounts = $account->subAccounts;
        $transactions = $account->transactions;

        return view('client.dashboard', compact('client', 'account', 'subAccounts', 'transactions', 'tontinePlans'));
    }

        // Traitement des dépôts et retraits
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'sub_account_id' => 'nullable|exists:sub_accounts,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:500',
            'description' => 'nullable|string|max:255'
        ]);

        $client = Auth::user();
        $account = $client->accounts()->where('id', $request->account_id)->first();
        $amount = $request->amount;
        $fees = 0;

        if (!$account) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        // --- LOGIQUE DES RETRAITS (WITHDRAWAL) ---
        if ($request->type === 'withdrawal') {
            if ($amount < 5000) {
                return redirect()->back()->with('error', 'Le montant minimum pour un retrait est de 5 000 XAF.');
            }

            $fees = ceil($amount / 25000) * 500;
            $totalDeduction = $amount + $fees;

            if ($request->sub_account_id) {
                $subAccount = $account->subAccounts()->where('id', $request->sub_account_id)->firstOrFail();
                if ($subAccount->balance < $totalDeduction) {
                    return redirect()->back()->with('error', "Solde insuffisant dans cette tontine. Requis (avec frais de {$fees} XAF) : " . number_format($totalDeduction) . " XAF.");
                }
            }

            if ($account->balance < $totalDeduction) {
                return redirect()->back()->with('error', "Solde général insuffisant pour couvrir le retrait et les frais de {$fees} XAF.");
            }
        }

        DB::beginTransaction();

        try {
            if ($request->type === 'deposit') {
                // 1. Augmenter le solde principal
                $account->increment('balance', $amount);

                // 2. Si une tontine/sous-compte est ciblé
                if ($request->sub_account_id) {
                    $subAccount = $account->subAccounts()->where('id', $request->sub_account_id)->firstOrFail();
                    $subAccount->increment('balance', $amount);

                    // --- SYNCHRONISATION COMMANDE / TonTINE ARTICLE ---
                    // On cherche la commande active rattachée à ce produit / sous-compte pour ce client
                    $order = Order::where('user_id', $client->id)
                        ->whereNotIn('status', ['completed', 'defaulted'])
                        ->latest()
                        ->first();

                    if ($order) {
                        // a. Enregistrement dans l'historique des paiements de la commande
                        OrderPayment::create([
                            'order_id'     => $order->id,
                            'collected_by' => auth()->id(),
                            'amount'       => $amount,
                        ]);

                        // b. Incrémentation du montant versé sur la commande
                        $order->paid_amount += $amount;
                        $order->last_payment_at = now();

                        // c. Passage automatique du statut si 60% atteint
                        if ($order->paid_amount >= $order->threshold_60_amount && $order->status === 'in_progress') {
                            $order->status = 'eligible_for_delivery';
                        }

                        // d. Finalisation si 100% totalement soldé
                        if ($order->paid_amount >= $order->total_amount) {
                            $order->status = 'completed';
                        }

                        $order->save();
                    }

                    if ($subAccount->target_amount > 0 && $subAccount->balance >= $subAccount->target_amount) {
                        $subAccount->update(['status' => 'completed']);
                    }
                }

            } else {
                // Retrait : On déduit le Montant + les Frais
                $account->decrement('balance', $totalDeduction);

                if ($request->sub_account_id) {
                    $subAccount = $account->subAccounts()->where('id', $request->sub_account_id)->firstOrFail();
                    $subAccount->decrement('balance', $totalDeduction);
                }
            }

            // 3. Enregistrer la transaction globale
            $account->transactions()->create([
                'sub_account_id' => $request->sub_account_id,
                'type'           => $request->type,
                'amount'         => $amount,
                'fees'           => $fees,
                'description'    => $request->description,
                'status'         => 'completed',
                'reference'      => 'TXN-' . strtoupper(uniqid()),
                'performed_by'   => auth()->id(),
            ]);

            DB::commit();

            $message = $request->type === 'deposit'
                ? 'Dépôt effectué et versement synchronisé avec la commande !'
                : "Retrait effectué avec succès. Frais appliqués : {$fees} XAF.";

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Une erreur est survenue lors du traitement : ' . $e->getMessage());
        }
    }

    // Traitement de la souscription à une nouvelle tontine / sous-compte
    public function storeSubAccount(Request $request)
    {
        // 1. Validation des données entrantes (on attend maintenant le tontine_plan_id)
        $request->validate([
            'account_id'      => 'required|exists:accounts,id',
            'tontine_plan_id' => 'required|exists:tontine_plans,id',
            'name'            => 'required|string|max:100', // Ex: Mon objectif moto
            'target_amount'   => 'required|numeric|min:500',
        ]);

        // 2. Récupération du plan de tontine sélectionné pour récupérer sa couleur par défaut
        $tontinePlans = Tontine_plan::findOrFail($request->tontine_plan_id);
        // On récupère le plan sélectionné par le client depuis la BDD
        $plan = \App\Models\Tontine_plan::findOrFail($request->tontine_plan_id);
        // Securité : Vérifier que le compte appartient bien à l'utilisateur connecté
        $account = auth()->user()->accounts()->where('id', $request->account_id)->first();
        if (!$account) {
            return redirect()->back()->with('error', 'Action non autorisée sur ce compte.');
        }

        // 3. Génération d'un code unique incrémental pour le sous-compte (A, B, C...)
        $existingCount = \App\Models\SubAccount::where('account_id', $account->id)->count();
        $code = chr(65 + ($existingCount % 26)); // Limite de sécurité à 26 lettres (A-Z)

        // 4. Création du sous-compte avec la liaison au plan
        SubAccount::create([
            'account_id'      => $account->id,
            'tontine_plan_id' => $plan->id, // Liaison cruciale à ton catalogue de tontines
            'name'            => $request->name,
            'code'            => $code,
            'balance'         => 0.00,
            'target_amount'   => $request->target_amount,
            'color'           => $plan->default_color, // On utilise la couleur du plan
            'status'          => 'active'
        ]);

        // 5. Redirection avec message de succès
        return redirect()->route('client.dashboard')->with('success', 'Votre souscription à la ' . $plan->name . ' a été enregistrée avec succès !');
    }
}
