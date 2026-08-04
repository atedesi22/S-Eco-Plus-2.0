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
        Schema::create('cash_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->unique(); // Ex: VER-2026-0089
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agency_id')->constrained('structures')->onDelete('cascade');
            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('set null'); // Caissier qui valide

            $table->decimal('amount', 15, 2);
            $table->enum('deposit_type', ['frais_dossier', 'vente_boutique', 'collecte_globale', 'autre'])->default('autre');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->string('receipt_photo')->nullable(); // Photo du bordereau physiquement signé
            $table->text('notes')->nullable();
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_deposits');
    }
};
