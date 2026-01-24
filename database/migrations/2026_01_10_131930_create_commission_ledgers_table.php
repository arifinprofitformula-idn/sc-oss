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
        Schema::create('commission_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // REGISTRATION, TRANSACTION, ADJUSTMENT, PAYOUT
            $table->decimal('amount', 18, 2);
            $table->string('status')->default('PENDING'); // PENDING, AVAILABLE, PAID, CANCELLED
            $table->nullableMorphs('reference'); // Order, Payout, User (for registration)
            $table->string('description')->nullable();
            $table->timestamp('available_at')->nullable(); // For holding period
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_ledgers');
    }
};
