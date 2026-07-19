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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Liaison au compte impacté
            $table->foreignId('account_id')->constrained()->onDelete('cascade');

            // Qui a exécuté ou initié la transaction (un agent, une collectrice, ou le client lui-même)
            $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');

            // Types d'opérations financières
            $table->enum('type', [
                'deposit', 'withdrawal', 'account_maintenance_fee',
                'withdrawal_fee', 'transfiguration', 'product_payment'
            ]);

            $table->decimal('amount', 15, 2);
            $table->decimal('fees', 15, 2)->default(0.00); // Pour tracer les frais coupés séparément

            // Référence unique pour traçabilité (Rapprochement Mobile Money / GAB / Caisse)
            $table->string('reference')->unique();

            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
