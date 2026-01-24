<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('day');
            $table->time('open')->nullable();
            $table->time('close')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            $table->unique(['store_id','day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_operating_hours');
    }
};

