<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up duplicates first to ensure index creation succeeds
        $duplicates = DB::table('commission_ledgers')
            ->select('user_id', 'type', 'reference_type', 'reference_id', DB::raw('MIN(id) as min_id'))
            ->whereNotNull('reference_id')
            ->groupBy('user_id', 'type', 'reference_type', 'reference_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('commission_ledgers')
                ->where('user_id', $dup->user_id)
                ->where('type', $dup->type)
                ->where('reference_type', $dup->reference_type)
                ->where('reference_id', $dup->reference_id)
                ->where('id', '>', $dup->min_id)
                ->delete();
        }

        Schema::table('commission_ledgers', function (Blueprint $table) {
            // Unique constraint: one commission per user per reference per type
            $table->unique(['user_id', 'reference_type', 'reference_id', 'type'], 'unique_commission_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_ledgers', function (Blueprint $table) {
            $table->dropUnique('unique_commission_entry');
        });
    }
};
