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
        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('gender')->nullable(); // 'Laki-laki', 'Perempuan'
                $table->string('photo_path')->nullable();
                $table->string('job')->nullable();
                $table->string('religion')->nullable();
                $table->string('birth_place')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('marital_status')->nullable();
                $table->timestamps();
            });
        } else {
            // If table exists, ensure columns exist (just in case)
             Schema::table('user_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('user_profiles', 'birth_place')) {
                    $table->string('birth_place')->nullable();
                }
                if (!Schema::hasColumn('user_profiles', 'birth_date')) {
                    $table->date('birth_date')->nullable();
                }
                if (!Schema::hasColumn('user_profiles', 'marital_status')) {
                    $table->string('marital_status')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop it automatically to avoid data loss if it was just a fix
    }
};
