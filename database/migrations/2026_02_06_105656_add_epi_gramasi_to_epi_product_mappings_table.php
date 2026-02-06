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
        Schema::table('epi_product_mappings', function (Blueprint $table) {
            $table->decimal('epi_gramasi', 10, 3)->default(1)->after('epi_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('epi_product_mappings', function (Blueprint $table) {
            $table->dropColumn('epi_gramasi');
        });
    }
};
