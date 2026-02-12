<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'email_provider',
                'value' => 'brevo',
                'group' => 'email',
                'type' => 'select',
                'description' => 'Active Email Provider',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mailketing_api_token',
                'value' => '',
                'group' => 'mailketing',
                'type' => 'encrypted',
                'description' => 'API Token from Mailketing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mailketing_sender_email',
                'value' => 'no-reply@example.com',
                'group' => 'mailketing',
                'type' => 'text',
                'description' => 'Default Sender Email for Mailketing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mailketing_sender_name',
                'value' => 'EPI Admin',
                'group' => 'mailketing',
                'type' => 'text',
                'description' => 'Default Sender Name for Mailketing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Use insertOrIgnore to avoid duplicates if migration is re-run or keys exist
        DB::table('system_settings')->insertOrIgnore($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')
            ->whereIn('key', [
                'email_provider',
                'mailketing_api_token',
                'mailketing_sender_email',
                'mailketing_sender_name'
            ])
            ->delete();
    }
};
