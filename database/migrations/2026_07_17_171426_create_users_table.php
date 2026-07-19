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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('profile_photo')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique(); // Crucial pour les clients/agents de terrain au Cameroun
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->foreignId('structure_id')->nullable()->constrained('structures')->onDelete('set null');

            // --- RELATIONS TERRITORIALES DE S ECO PLUS ---
            // Un utilisateur (personnel ou client) est affilié à une agence et optionnellement à une zone
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');

            // --- PARAMÈTRES DE SÉCURITÉ ---
            $table->boolean('mfa_enabled')->default(false); // Pour l'authentification à deux facteurs
            $table->enum('status', ['active', 'suspended'])->default('active'); // Contrôle d'accès global

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
