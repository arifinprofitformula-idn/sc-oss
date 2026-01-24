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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // 1. Identitas Toko
            $table->string('name');
            $table->string('slug')->unique(); // for public url if needed
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            
            // 2. Informasi Kontak & Alamat (Rajaongkir Integrated)
            $table->text('address')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('subdistrict_id')->nullable();
            $table->string('postal_code')->nullable();
            
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->json('social_links')->nullable(); // {facebook, instagram, tiktok}
            
            // 3. Jam Operasional
            $table->json('operating_hours')->nullable(); // {monday: {open: '08:00', close: '17:00', is_closed: false}}
            $table->boolean('is_open')->default(true); // Manual override
            $table->boolean('holiday_mode')->default(false);
            
            // 4. Informasi Pembayaran
            $table->json('bank_details')->nullable(); // [{bank: 'BCA', number: '123', name: 'John'}]
            $table->json('payment_methods')->nullable(); // ['transfer', 'ewallet']
            
            $table->text('notification_templates')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
