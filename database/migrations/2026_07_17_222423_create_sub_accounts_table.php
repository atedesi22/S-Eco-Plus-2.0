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
        Schema::create('sub_accounts', function (Blueprint $table) {
            $table->id();
            // Clé étrangère vers le compte d'épargne principal
            $table->foreignId('account_id')->constrained()->onDelete('cascade');

            $table->string('name'); // Ex: Tontine Projet Immobilier, Tontine Électroménager
            $table->string('code', 10); // Ex: A, B, C pour l'indexation du numéro de sous-compte
            $table->decimal('balance', 15, 2)->default(0.00); // Solde actuel de cette tontine
            $table->decimal('target_amount', 15, 2)->nullable(); // Montant objectif (Ex: 1 500 000 XAF)
            $table->string('color')->default('emerald'); // Couleur Tailwind pour l'UI (blue, amber, etc.)
            $table->string('status')->default('active'); // active, completed, suspended
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_accounts');
    }
};
