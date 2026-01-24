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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 18, 2)->after('total_amount')->default(0);
            $table->decimal('tax_amount', 18, 2)->after('subtotal')->default(0);
            $table->decimal('shipping_cost', 18, 2)->after('tax_amount')->default(0);
            $table->string('shipping_courier')->nullable()->after('shipping_address');
            $table->string('shipping_service')->nullable()->after('shipping_courier');
            $table->string('payment_method')->nullable()->after('shipping_service');
            $table->json('payload')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'tax_amount',
                'shipping_cost',
                'shipping_courier',
                'shipping_service',
                'payment_method',
                'payload',
            ]);
        });
    }
};
