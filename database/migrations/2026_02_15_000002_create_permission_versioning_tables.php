<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('action'); // created, updated, deleted
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();
            $table->index(['permission_id']);
        });

        Schema::create('role_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('action');
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();
            $table->index(['role_id']);
        });

        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('role_versions');
        Schema::dropIfExists('permission_versions');
    }
};

