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
        // Drop StoreShippingOption table
        Schema::dropIfExists('store_shipping_options');

        // Drop columns from stores table if they exist
        Schema::table('stores', function (Blueprint $table) {
            $columnsToDrop = [
                'couriers',
                'enable_same_day',
                'base_shipping_cost',
                'use_unique_code',
                'invoice_prefix',
                'terms_conditions'
            ];
            
            $existingColumns = [];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('stores', $column)) {
                    $existingColumns[] = $column;
                }
            }

            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('store_shipping_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->json('couriers')->nullable();
            $table->timestamps();
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->json('couriers')->nullable();
            $table->boolean('enable_same_day')->default(false);
            $table->decimal('base_shipping_cost', 12, 2)->nullable();
            $table->boolean('use_unique_code')->default(false);
            $table->string('invoice_prefix')->nullable();
            $table->text('terms_conditions')->nullable();
        });
    }
};
