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
            // Modify existing columns if needed or add new ones
            // nik and whatsapp were added in previous migration but might need modification
            // We need to ensure uniqueness for nik
            
            // Check if column exists before modifying, but since we are in dev, we can assume standard flow
            // However, migration '2026_01_11_000001' added nik as nullable.
            // We need to change it to unique.
            
            // Adding silver_channel_id
            $table->string('silver_channel_id')->nullable()->unique()->after('id');
            
            // Make sure nik is unique (if not already)
            // Note: modifying columns requires dbal/doctrine usually, or just raw sql.
            // But in Laravel 11, it's easier.
            // Since previous migration made it nullable string(16), we add unique index here.
            $table->unique('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('silver_channel_id');
            $table->dropUnique(['nik']);
        });
    }
};
