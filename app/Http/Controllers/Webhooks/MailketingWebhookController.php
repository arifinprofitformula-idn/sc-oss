<?php
declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEventJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MailketingWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        ProcessWebhookEventJob::dispatch($request->all());
        return response('', 204);
    }
}

