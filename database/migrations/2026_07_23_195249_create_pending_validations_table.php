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
        Schema::create('pending_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->constrained('structures')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');

            // Types de demandes : dépassement plafond, ouverture/fermeture caisse, virement coffre, etc.
            $table->string('type'); // Ex: ceiling_exceeded, cash_transfer, account_unlock
            $table->text('description');
            $table->decimal('amount', 15, 2)->nullable();

            // Données contextuelles sous forme JSON (compte source, compte dest, etc.)
            $table->json('payload')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_validations');
    }
};
