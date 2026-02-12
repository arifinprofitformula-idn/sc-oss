<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\EmailLog;

class EmailTrackingController extends Controller
{
    public function open(string $id)
    {
        $log = EmailLog::where('provider_message_id', $id)->first();
        if ($log) {
            $log->increment('opens_count');
        }
        Log::info("Email opened: {$id}");
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=');
        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function click(string $id, Request $request)
    {
        $url = $request->query('u');
        $log = EmailLog::where('provider_message_id', $id)->first();
        if ($log) {
            $log->increment('clicks_count');
        }
        Log::info("Email clicked: {$id}", ['url' => $url]);
        return redirect()->away($url ?? url('/'));
    }
}
