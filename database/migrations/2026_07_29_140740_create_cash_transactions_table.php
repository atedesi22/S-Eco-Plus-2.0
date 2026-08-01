<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();

            // Identifiant unique de la transaction (ex: TXN-20260729-001)
            $table->string('transaction_number')->unique();

            // Agence concernée
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');

            // Tiers / Client impliqué (nullable si opération interne de caisse)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Agent / Opérateur ayant effectué ou validé l'opération
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');

            // Sens de la transaction : 'credit' (entrée) ou 'debit' (sortie)
            $table->enum('type', ['credit', 'debit']);

            // Catégorie fonctionnelle de la transaction
            $table->string('category');
            // Ex: 'tontine_deposit', 'order_installment', 'cash_sale', 'client_withdrawal', 'cash_in', 'cash_out'

            // Montant de la transaction (en FCFA/XAF)
            $table->bigInteger('amount');

            // Solde calculé de la caisse/compte après l'opération (optionnel pour traçabilité)
            $table->bigInteger('balance_after')->default(0);

            // Mode de règlement ('cash', 'mobile_money', 'bank_transfer', etc.)
            $table->string('payment_method')->default('cash');

            // Libellé / Remarque / Motif de l'opération
            $table->text('description')->nullable();

            // ID de référence croisée (ID de commande, ID de tontine, etc.)
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->timestamps();

            // Index pour optimiser les filtres et recherches fréquents
            $table->index(['agency_id', 'type']);
            $table->index(['agency_id', 'category']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
