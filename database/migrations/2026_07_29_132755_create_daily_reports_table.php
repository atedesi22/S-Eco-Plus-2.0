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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();

            // Rattachement à l'agence/structure
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');

            // Date spécifique du rapport journalier
            $table->date('report_date');

            // Indicateurs Financiers (en monnaie entière XAF)
            $table->integer('total_field_collections')->default(0); // Total collecté sur le terrain par les agents
            $table->integer('total_cash_sales')->default(0);        // Total des ventes comptant boutique
            $table->integer('total_collected')->default(0);         // Somme globale consolidée (Terrain + Comptant)

            // Indicateurs d'Activité & Métier
            $table->integer('new_clients_count')->default(0);       // Nombre de nouveaux clients enregistrés
            $table->integer('deliveries_count')->default(0);        // Nombre de commandes/articles livrés (seuil 60%)

            // Traçabilité & Validation par le Directeur
            $table->text('notes')->nullable();                      // Remarques ou observations du directeur
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null'); // Directeur qui a clôturé/validé le rapport

            $table->timestamps();

            // Index et contrainte d'unicité : Un seul rapport quotidien archivé par agence et par jour
            $table->unique(['agency_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
