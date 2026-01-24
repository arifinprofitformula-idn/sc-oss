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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('distributor_name');
            $table->text('distributor_address')->nullable();
            $table->string('distributor_phone')->nullable();
            
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('subdistrict_id')->nullable();
            
            $table->string('logo_path')->nullable();
            $table->text('bank_info')->nullable();
            
            $table->boolean('unique_code_enabled')->default(true);
            $table->integer('unique_code_range_start')->default(1);
            $table->integer('unique_code_range_end')->default(999);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
