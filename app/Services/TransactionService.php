<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Audit;
use App\Models\CustomerTontine;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * Effectuer un dépôt sécurisé
     */

    public function deposit(int $accountId, int $amount, string $description = null): Transaction
    {
        if ($amount <= 0) {
            throw new Exception("Le montant du dépôt doit être supérieur à zéro.");
        }

        // Utilisation du mécanisme atomique DB::transaction
        return DB::transaction(function () use ($accountId, $amount, $description) {

            // Verrouillage de la ligne pour éviter la concurrence (Race Condition)
            $account = Account::lockForUpdate()->findOrFail($accountId);

            if ($account->status === 'frozen') {
                throw new Exception("Opération impossible : Ce compte a été gelé par la conformité.");
            }

            $balanceBefore = $account->balance;

            // Mise à jour du solde
            $account->balance += $amount;
            $account->save();

            // Création du reçu de transaction immuable
            $transaction = Transaction::create([
                'reference' => 'TX-DEP-' . strtoupper(Str::random(10)),
                'account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $amount,
                'performed_by' => Auth::id(),
                'agency_id' => Auth::user()->agency_id ?? $account->user->agency_id,
                'description' => $description,
            ]);

            // Alimentation forcée du Journal d'Audit
            $this->logAudit('DEPOSIT', $account, $balanceBefore, $account->balance);

            return $transaction;
        });
    }

    /**
     * Effectuer un retrait avec application de frais métiers
     */
    public function withdraw(int $accountId, float $amount, string $description = null): Transaction
    {
        if ($amount <= 5000) {
            throw new Exception("Le montant du retrait doit être supérieur à 5000 XAF.");
        }

        return DB::transaction(function () use ($accountId, $amount, $description) {

            // Verrouillage de la ligne pour éviter la concurrence
            $account = Account::lockForUpdate()->findOrFail($accountId);

            if ($account->status === 'suspended' || $account->status === 'closed') {
                throw new Exception("Ce compte n'est pas actif ou suspendu. Opération refusée.");
            }

            // =================================================================
            // APPLICATION DE LA RÈGLE DES FRAIS PAR PALIER DE 25 000 XAF
            // =================================================================
            $fraisParPalier = 500.00; // Ajustez ce montant selon vos grilles réelles (Ex: 150 XAF)
            $nombreDePaliers = ceil($amount / 25000);
            $totalFees = $nombreDePaliers * $fraisParPalier;

            // Le montant total à retirer du solde (retrait + frais de palier)
            $totalDeduction = $amount + $totalFees;

            // Règle de sécurité : Vérification du solde disponible + respect du fond de caisse (reserve_fund)
            $soldeDisponible = $account->balance - $account->reserve_fund;

            if ($soldeDisponible < $totalDeduction) {
                throw new Exception("Solde disponible insuffisant (Fonds de réserve de " . $account->reserve_fund . " XAF inclus).");
            }

            // Vérification du verrouillage temporaire de la tontine
            if ($account->locked_until && now()->lt($account->locked_until)) {
                throw new Exception("Ce compte d'épargne est bloqué jusqu'au " . $account->locked_until->format('d/m/Y'));
            }

            $balanceBefore = $account->balance;

            // Déduction sur le compte
            $account->balance -= $totalDeduction;
            $account->save();

            // Création du reçu de transaction avec vos colonnes exactes ('amount' et 'fees')
            $transaction = Transaction::create([
                'account_id' => $account->id,
                'performed_by' => Auth::id(),
                'type' => 'withdrawal',
                'amount' => $amount,
                'fees' => $totalFees, // Vos frais dynamiques par tranche sont tracés ici !
                'reference' => 'TX-WIT-' . strtoupper(Str::random(12)),
                'description' => $description ?? "Retrait de " . number_format($amount) . " XAF avec " . number_format($totalFees) . " XAF de frais de palier.",
            ]);

            // Alimentation du journal d'audit avec les valeurs décimales exactes
            Audit::create([
                'action' => 'WITHDRAWAL',
                'user_id' => Auth::id(),
                'agency_id' => Auth::user()->agency_id, // Lié à l'agence de la secrétaire/comptable
                'account_id' => $account->id,
                'balance_before' => $balanceBefore,
                'balance_after' => $account->balance,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent(),
            ]);

            return $transaction;
        });
    }
    /**
     * Fonction interne d'alimentation automatique du Journal d'Audit
     */
    private function logAudit(string $action, Account $account, int $before, int $after): void
    {
        Audit::create([
            'action' => $action,
            'user_id' => Auth::id(),
            'agency_id' => Auth::user()->agency_id ?? $account->user->agency_id,
            'account_id' => $account->id,
            'balance_before' => $before,
            'balance_after' => $after,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'POS-Terminal',
        ]);
    }

    // Extrait de code à intégrer dans la fonction de création de transaction (Ex: TransactionController ou TransactionService)
    public function processDeposit(User $client, $amount, $performedBy)
    {
        // Vérifier s'il y a un emprunt actif hors délai
        $overdueEmprunt = CustomerTontine::where('user_id', $client->id)
            ->where('is_active', true)
            ->where('deadline_date', '<', now())
            ->whereColumn('amount_reimbursed', '<', 'amount_to_reimburse')
            ->first();

        if ($overdueEmprunt) {
            $remainingDebt = $overdueEmprunt->amount_to_reimburse - $overdueEmprunt->amount_reimbursed;

            // Calcul du montant affectable au remboursement
            $paymentToApply = min($amount, $remainingDebt);

            // Appliquer le remboursement
            $overdueEmprunt->increment('amount_reimbursed', $paymentToApply);

            // Enregistrer l'écriture dans le Grand Livre comme remboursement d'emprunt forcé
            Transaction::create([
                'user_id' => $client->id,
                'amount' => $paymentToApply,
                'type' => 'remboursement_emprunt_force',
                'performed_by' => $performedBy,
            ]);

            // Si l'emprunt est totalement soldé suite à ce paiement : Suppression/Désactivation automatique
            if ($overdueEmprunt->fresh()->amount_reimbursed >= $overdueEmprunt->amount_to_reimburse) {
                $overdueEmprunt->delete(); // Suppression ou passage à is_active = false
            }

            // S'il reste un résidu de l'argent déposé après avoir soldé la dette, le reste va sur son compte normal
            $leftover = $amount - $paymentToApply;
            if ($leftover > 0) {
                // Continuer le dépôt standard pour le montant '$leftover'
            }
        } else {
            // Traitement normal de la transaction standard
        }
    }
    }
