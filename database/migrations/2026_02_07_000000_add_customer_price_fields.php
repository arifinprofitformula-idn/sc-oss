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
            $table->decimal('price_customer', 15, 2)->nullable()->comment('Harga untuk konsumen umum')->after('price_silverchannel');
        });

        Schema::table('epi_product_mappings', function (Blueprint $table) {
            $table->unsignedInteger('epi_level_id_customer')->nullable()->after('epi_level_id')->comment('EPI APE Level ID for Consumer Price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_customer');
        });

        Schema::table('epi_product_mappings', function (Blueprint $table) {
            $table->dropColumn('epi_level_id_customer');
        });
    }
};
