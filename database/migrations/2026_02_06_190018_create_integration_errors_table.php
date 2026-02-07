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
        Schema::create('integration_errors', function (Blueprint $table) {
            $table->id();
            $table->string('integration')->index(); // e.g., 'epi_ape'
            $table->string('error_code')->nullable(); // e.g., 'API_TIMEOUT', 'PRICE_MISMATCH'
            $table->text('message');
            $table->json('details')->nullable();
            $table->enum('status', ['new', 'resolved', 'ignored'])->default('new');
            $table->text('recommended_action')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_errors');
    }
};
