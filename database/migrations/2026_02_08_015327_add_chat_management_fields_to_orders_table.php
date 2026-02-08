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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_assigned_to')->nullable()->after('proof_of_delivery');
            $table->json('chat_tags')->nullable()->after('chat_assigned_to');
            $table->enum('chat_priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('chat_tags');

            $table->foreign('chat_assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['chat_assigned_to']);
            $table->dropColumn(['chat_assigned_to', 'chat_tags', 'chat_priority']);
        });
    }
};
