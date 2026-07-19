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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // Ex: PRD-2026-001
            $table->string('name');
            $table->text('description')->nullable();

            // Tarification en monnaie entière (XAF)
            $table->integer('purchase_price'); // Prix d'achat fournisseur
            $table->integer('selling_price');  // Prix de vente public

            // Gestion de stock
            $table->integer('stock')->default(0);
            $table->integer('alert_threshold')->default(5); // Seuil d'alerte stock bas

            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
