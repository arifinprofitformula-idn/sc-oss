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
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'bank_details')) {
                $table->json('bank_details')->nullable()->after('holiday_mode');
            }
            if (!Schema::hasColumn('stores', 'payment_methods')) {
                $table->json('payment_methods')->nullable()->after('bank_details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['bank_details', 'payment_methods']);
        });
    }
};
