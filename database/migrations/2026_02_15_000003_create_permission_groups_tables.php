<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_group_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_group_id');
            $table->string('permission_name');
            $table->timestamps();
            $table->foreign('permission_group_id')->references('id')->on('permission_groups')->onDelete('cascade');
            $table->index(['permission_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_group_items');
        Schema::dropIfExists('permission_groups');
    }
};

