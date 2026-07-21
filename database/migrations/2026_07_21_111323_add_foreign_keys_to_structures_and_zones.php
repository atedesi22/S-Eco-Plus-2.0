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
        // 1. Ajout du Directeur sur Structures
        Schema::table('structures', function (Blueprint $table) {
            $table->foreignId('director_id')->nullable()->after('parent_id')->constrained('users')->onDelete('set null');
        });

        // 2. Ajout du Chef Commercial / Manager sur Zones
        Schema::table('zones', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('structure_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropForeign(['director_id']);
            $table->dropColumn('director_id');
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
