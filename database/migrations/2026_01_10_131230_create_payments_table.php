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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 18, 2);
            $table->string('method'); // manual, midtrans, etc.
            $table->string('status')->default('PENDING'); // PENDING, PAID, FAILED, EXPIRED, REFUNDED
            $table->string('proof_file')->nullable(); // For manual transfer
            $table->string('external_id')->nullable(); // For payment gateways
            $table->json('payload')->nullable(); // Store gateway response
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
