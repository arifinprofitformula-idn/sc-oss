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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('commission_enabled')->default(false)->after('is_active');
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage')->after('commission_enabled');
            $table->decimal('commission_value', 12, 2)->default(0)->after('commission_type');
        });

        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Referrer who gets the commission');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            $table->decimal('commission_amount', 12, 2);
            $table->enum('commission_type', ['percentage', 'fixed']);
            $table->decimal('commission_value', 12, 2)->comment('The rate used at time of calculation');
            $table->decimal('product_price', 12, 2)->comment('Price at time of calculation');
            $table->integer('quantity')->default(1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_logs');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['commission_enabled', 'commission_type', 'commission_value']);
        });
    }
};
