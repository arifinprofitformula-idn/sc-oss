<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_follow_ups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_user_id');
            $table->string('status', 50)->default('PENDING');
            $table->timestamp('last_follow_up_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['referrer_id', 'referred_user_id']);
            $table->index('status');
            $table->index('next_follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_follow_ups');
    }
};

