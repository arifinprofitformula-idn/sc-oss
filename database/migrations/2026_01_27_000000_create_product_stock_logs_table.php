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
        Schema::create('product_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->comment('User who performed the action');
            $table->string('type')->default('manual_adjustment')->comment('initial, manual_adjustment, order, refund, etc');
            $table->integer('quantity')->comment('Change amount (positive or negative)');
            $table->integer('final_stock')->comment('Stock after adjustment');
            $table->string('note')->nullable();
            $table->json('meta')->nullable()->comment('Additional data like order_id, etc');
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_logs');
    }
};
