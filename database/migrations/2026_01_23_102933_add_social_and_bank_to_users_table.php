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
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_facebook')->nullable()->after('marital_status');
            $table->string('social_instagram')->nullable()->after('social_facebook');
            $table->string('social_tiktok')->nullable()->after('social_instagram');
            $table->string('social_thread')->nullable()->after('social_tiktok');
            $table->string('bank_name')->nullable()->after('social_thread');
            $table->string('bank_account_no')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'social_facebook',
                'social_instagram',
                'social_tiktok',
                'social_thread',
                'bank_name',
                'bank_account_no',
                'bank_account_name'
            ]);
        });
    }
};
