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
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('bank_details');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['bank_details', 'payment_methods']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->json('bank_details')->nullable();
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->json('bank_details')->nullable();
            $table->json('payment_methods')->nullable();
        });
    }
};
