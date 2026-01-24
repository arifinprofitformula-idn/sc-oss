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
        Schema::create('external_apis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('endpoint_url');
            $table->string('method'); // GET, POST, PUT, DELETE
            $table->json('parameters')->nullable();
            $table->string('auth_type')->default('none'); // none, api_key, bearer, basic
            $table->text('auth_credentials')->nullable(); // encrypted json
            $table->integer('rate_limit_requests')->default(60);
            $table->integer('rate_limit_period')->default(60); // in seconds
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_apis');
    }
};
