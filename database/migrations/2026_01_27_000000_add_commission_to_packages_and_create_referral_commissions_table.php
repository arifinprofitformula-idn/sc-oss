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
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('commission_type', ['percentage', 'fixed'])->default('fixed')->after('price');
            $table->decimal('commission_value', 12, 2)->default(0)->after('commission_type');
        });

        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->enum('commission_type', ['percentage', 'fixed']);
            $table->decimal('commission_base_amount', 12, 2)->comment('Price of the package at the time');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED, PAID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_value']);
        });
    }
};
