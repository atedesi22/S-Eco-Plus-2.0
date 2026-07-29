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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Ex: CMD-2026-0089
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Le client
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Le produit
            $table->foreignId('collector_id')->nullable()->constrained('users')->onDelete('set null'); // Agent référent

            $table->enum('payment_type', ['cash', 'installment'])->default('installment');
            $table->integer('total_amount'); // Prix total échelonné ou cash
            $table->integer('paid_amount')->default(0); // Cumul versé
            $table->integer('threshold_60_amount'); // Montant exact correspondant aux 60%

            // Statuts
            $table->enum('status', ['in_progress', 'eligible_for_delivery', 'delivered', 'completed', 'defaulted'])->default('in_progress');
            $table->boolean('delivered_approved_by_director')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
