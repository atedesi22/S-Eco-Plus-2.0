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
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->constrained('structures')->onDelete('cascade');
            $table->string('name'); // Ex: Caisse Guichet 1, Coffre Fort Principal
            $table->enum('type', ['guichet', 'coffre_fort', 'virtuelle'])->default('guichet');

            // Agent caissier à qui la caisse est actuellement assignée
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('max_limit', 15, 2)->default(5000000.00); // Plafond encaisse autorisé

            $table->enum('status', ['open', 'closed', 'suspended'])->default('closed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
