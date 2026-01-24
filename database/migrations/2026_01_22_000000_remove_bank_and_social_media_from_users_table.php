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
            $columnsToDrop = [];
            
            if (Schema::hasColumn('users', 'bank_name')) {
                $columnsToDrop[] = 'bank_name';
            }
            if (Schema::hasColumn('users', 'bank_account_no')) {
                $columnsToDrop[] = 'bank_account_no';
            }
            if (Schema::hasColumn('users', 'bank_account_name')) {
                $columnsToDrop[] = 'bank_account_name';
            }
            if (Schema::hasColumn('users', 'social_media')) {
                $columnsToDrop[] = 'social_media';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('marital_status');
            $table->text('bank_account_no')->nullable()->after('bank_name');
            $table->text('bank_account_name')->nullable()->after('bank_account_no');
            $table->json('social_media')->nullable()->after('bank_account_name');
        });
    }
};
