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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Ex: AG-AKWA-01
            $table->string('location');

            // Relation réflexive pour les agences mères / filles
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('agencies')
                  ->onDelete('restrict'); // Empêche de supprimer une agence mère si elle a des filiales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
