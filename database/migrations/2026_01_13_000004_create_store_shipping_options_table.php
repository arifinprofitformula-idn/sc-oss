<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_shipping_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->json('couriers')->nullable();
            $table->timestamps();
            $table->unique('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_shipping_options');
    }
};

