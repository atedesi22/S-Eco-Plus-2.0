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
        Schema::create('cash_settlements', function (Blueprint $table) {
            $table->id();

            // L'agent (Collectrice, Commercial, Caissier) dont on arrête la caisse
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');

            // Le comptable / gestionnaire qui valide l'arrêté
            $table->foreignId('validated_by')->constrained('users')->onDelete('cascade');

            // Montants et écarts
            $table->decimal('expected_amount', 15, 2)->default(0); // Solde théorique système
            $table->decimal('declared_amount', 15, 2)->default(0); // Cash réel versé par l'agent
            $table->decimal('gap_amount', 15, 2)->default(0);      // Écart (positif = surplus, négatif = manquant)

            // Statut de la clôture
            $table->enum('status', ['conforme', 'manquant', 'surplus'])->default('conforme');
            $table->text('notes')->nullable(); // Remarques éventuelles du comptable

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_settlements');
    }
};
