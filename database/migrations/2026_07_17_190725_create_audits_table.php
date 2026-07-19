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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // 'DEPOSIT', 'WITHDRAWAL_REQUEST'
            $table->foreignId('user_id')->constrained(); // Qui a fait l'action
            $table->foreignId('agency_id')->constrained(); // Depuis quelle agence (Mère ou fille)

            // Données financières instantanées pour l'audit
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');

            // Métadonnées système
            $table->string('ip_address');
            $table->string('user_agent'); // Navigateur ou terminal POS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
