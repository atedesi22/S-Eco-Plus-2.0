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
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // L'employé concerné
            $table->string('role_name')->nullable();
            $table->string('title'); // Ex: Collecte mensuelle, Nouveaux comptes...
            $table->enum('type', ['collecte_amount', 'new_accounts', 'product_sales', 'credit_recovery']);

            // Seuils financiers ou numériques
            $table->bigInteger('target_value'); // Ce qu'il doit atteindre (ex: 5 000 000 XAF ou 50 comptes)
            $table->bigInteger('current_value')->default(0); // Progression actuelle

            // Périodicité
            $table->enum('period', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');

            $table->enum('status', ['in_progress', 'achieved', 'failed'])->default('in_progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objectives');
    }
};
