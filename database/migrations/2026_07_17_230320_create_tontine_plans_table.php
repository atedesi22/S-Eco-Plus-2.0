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
        Schema::create('tontine_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Tontine Scolaire
            $table->string('slug')->unique(); // Ex: scolaire, investissement, fin_annee
            $table->text('description')->nullable();
            $table->string('default_color')->default('emerald'); // Couleur UI par défaut
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Liaison : On met à jour la table sub_accounts pour pointer vers un plan de tontine
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->foreignId('tontine_plan_id')->nullable()->after('account_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->dropForeign(['tontine_plan_id']);
            $table->dropColumn('tontine_plan_id');
        });
        Schema::dropIfExists('tontine_plans');
    }
};
