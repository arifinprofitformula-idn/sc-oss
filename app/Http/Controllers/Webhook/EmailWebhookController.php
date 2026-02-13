<?php
declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailWebhookController extends Controller
{
    public function handle(Request $request, string $provider)
    {
        // Normalize payload across providers (brevo/mailketing)
        $payload = $request->all();
        // Example expected fields: message_id, event (delivered|bounce|open|click)
        app(\App\Listeners\EmailStatusUpdateListener::class)->handle($payload);

        Log::info('Email webhook processed', ['provider' => $provider, 'payload_keys' => array_keys($payload)]);
        return response()->json(['ok' => true]);
    }
}

