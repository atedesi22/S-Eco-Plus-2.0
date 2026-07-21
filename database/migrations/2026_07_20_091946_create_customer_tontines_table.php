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
        Schema::create('customer_tontines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Le Client
            $table->foreignId('tontine_plan_id')->constrained()->onDelete('cascade'); // Le Plan (ex: Tontine Emprunt)

            // Suivi financier de la dette / pénalité
            $table->bigInteger('amount_to_reimburse')->default(0); // Montant total à rembourser (la pénalité)
            $table->bigInteger('amount_reimbursed')->default(0); // Ce qui a déjà été retenu/cotisé

            // Métadonnées de l'échéance et de traçabilité
            $table->date('deadline_date'); // Date limite avant déclenchement automatique du prélèvement forcé
            $table->unsignedBigInteger('performed_by')->nullable(); // L'agent qui a déclenché/validé la liaison

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Clé étrangère pour l'agent (référence la table users)
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_tontines');
    }
};
