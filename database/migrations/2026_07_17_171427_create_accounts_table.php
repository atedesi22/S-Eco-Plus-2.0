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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number')->unique();

            // Les 7 types de tontine + option électroménager
            $table->enum('type', [
                'simple', 'scolaire', 'investissement', 'fin_annee',
                'assurance', 'islamique', 'marchande', 'electromenager'
            ]);
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');

            // Utilisation de decimal pour la précision financière (pas de float !)
            $table->decimal('balance', 15, 2)->default(0.00);

            // Le fond de caisse de 1000 XAF non retirant obligatoire à la création
            $table->decimal('reserve_fund', 15, 2)->default(1000.00);

            // Pour gérer les tontines bloquées temporairement
            $table->date('locked_until')->nullable();

            $table->enum('status', ['active', 'suspended', 'closed', 'frozen'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
