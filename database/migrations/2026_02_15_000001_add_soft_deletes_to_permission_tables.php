<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(config('permission.table_names.permissions'), function (Blueprint $table) {
            if (!Schema::hasColumn(config('permission.table_names.permissions'), 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn(config('permission.table_names.permissions'), 'description')) {
                $table->string('description')->nullable();
            }
        });

        Schema::table(config('permission.table_names.roles'), function (Blueprint $table) {
            if (!Schema::hasColumn(config('permission.table_names.roles'), 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn(config('permission.table_names.roles'), 'description')) {
                $table->string('description')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table(config('permission.table_names.permissions'), function (Blueprint $table) {
            if (Schema::hasColumn(config('permission.table_names.permissions'), 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn(config('permission.table_names.permissions'), 'description')) {
                $table->dropColumn('description');
            }
        });

        Schema::table(config('permission.table_names.roles'), function (Blueprint $table) {
            if (Schema::hasColumn(config('permission.table_names.roles'), 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn(config('permission.table_names.roles'), 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};

