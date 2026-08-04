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

            // Portée de l'objectif
            $table->foreignId('agency_id')->nullable()->constrained()->onDelete('cascade'); // Agence ciblée
            $table->string('role_name')->nullable(); // Ex: commercial, chef_commercial, collectrice, daf, dom
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Si individuel
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Qui a fixé l'objectif

            $table->string('title');
            $table->enum('type', ['new_accounts', 'collecte_amount', 'product_sales', 'credit_recovery']);

            // Cible & Prime
            $table->bigInteger('target_value'); // Ex: 50 comptes
            $table->decimal('base_bonus', 12, 2)->default(0); // Ex: 1000 XAF ou 1500 XAF

            // Période
            $table->enum('period', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
// $table->bigInteger('current_value')->default(0);

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
