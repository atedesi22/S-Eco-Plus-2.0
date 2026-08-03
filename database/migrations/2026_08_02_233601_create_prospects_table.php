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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');

            $table->string('full_name');
            $table->string('phone');
            $table->string('activity_sector')->nullable(); // Ex: Commerçant, Couturier, Enseignant...
            $table->string('location')->nullable(); // Ex: Marché Central, Allée B

            $table->enum('interest_type', ['tontine', 'epargne', 'article', 'credit'])->default('tontine');
            $table->decimal('estimated_budget', 15, 2)->default(0); // Budget d'épargne ou valeur d'article envisagé
            $table->enum('status', ['nouveau', 'en_discussion', 'converti', 'abandonne'])->default('nouveau');

            $table->text('notes')->nullable();
            $table->timestamp('next_contact_at')->nullable(); // Date de relance
            $table->foreignId('converted_user_id')->nullable()->constrained('users')->onDelete('set null'); // Lien si converti en client

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
