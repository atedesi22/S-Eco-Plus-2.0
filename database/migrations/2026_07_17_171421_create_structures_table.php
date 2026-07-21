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
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Direction Régionale Littoral" ou "Agence Akwa"
            $table->enum('type', ['regional_direction', 'agency']);

            // Relation hiérarchique : une agence dépend d'une direction régionale
            $table->foreignId('parent_id')->nullable()->constrained('structures')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};
