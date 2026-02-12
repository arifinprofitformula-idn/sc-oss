<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->string('type', 64);
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('to')->index();
            $table->string('status', 32)->index();
            $table->uuid('provider_message_id')->nullable()->index();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->unsignedInteger('opens_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->json('metadata')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'type', 'created_at']);
        });

        Schema::create('email_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 64);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'type']);
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 64);
            $table->json('segment')->nullable();
            $table->timestamp('schedule_at')->nullable();
            $table->string('status', 32)->default('DRAFT');
            $table->json('stats')->nullable();
            $table->timestamps();
            $table->index(['type', 'schedule_at', 'status']);
        });

        Schema::create('email_opens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('log_id');
            $table->string('user_agent')->nullable();
            $table->string('ip')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['log_id', 'occurred_at']);
        });

        Schema::create('email_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('log_id');
            $table->text('url')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['log_id', 'occurred_at']);
        });

        if (Schema::hasTable('email_templates')) {
            Schema::table('email_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('email_templates', 'language')) {
                    $table->string('language', 8)->nullable()->index();
                }
                if (!Schema::hasColumn('email_templates', 'type')) {
                    $table->string('type', 64)->nullable()->index();
                }
                if (!Schema::hasColumn('email_templates', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
            });
        }

        if (Schema::hasTable('email_template_histories')) {
            Schema::table('email_template_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('email_template_histories', 'variant')) {
                    $table->string('variant', 1)->nullable(); // 'A' or 'B'
                }
                if (!Schema::hasColumn('email_template_histories', 'split_ratio')) {
                    $table->unsignedTinyInteger('split_ratio')->default(50);
                }
                if (!Schema::hasColumn('email_template_histories', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_clicks');
        Schema::dropIfExists('email_opens');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_preferences');
        Schema::dropIfExists('email_logs');

        if (Schema::hasTable('email_template_histories')) {
            Schema::table('email_template_histories', function (Blueprint $table) {
                if (Schema::hasColumn('email_template_histories', 'variant')) {
                    $table->dropColumn('variant');
                }
                if (Schema::hasColumn('email_template_histories', 'split_ratio')) {
                    $table->dropColumn('split_ratio');
                }
                if (Schema::hasColumn('email_template_histories', 'notes')) {
                    $table->dropColumn('notes');
                }
            });
        }
        if (Schema::hasTable('email_templates')) {
            Schema::table('email_templates', function (Blueprint $table) {
                if (Schema::hasColumn('email_templates', 'language')) {
                    $table->dropColumn('language');
                }
                if (Schema::hasColumn('email_templates', 'type')) {
                    $table->dropColumn('type');
                }
                if (Schema::hasColumn('email_templates', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};

