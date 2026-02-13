<?php

declare(strict_types=1);

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailHistoryController extends Controller
{
    public function index(Request $request)
    {
        $logs = EmailLog::where('user_id', Auth::id())
            ->when($request->query('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->query('type'), function ($query, $type) {
                $query->where('type', 'like', "%{$type}%");
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('silverchannel.email_history.index', compact('logs'));
    }

    public function show(EmailLog $emailLog)
    {
        if ($emailLog->user_id !== Auth::id()) {
            abort(403);
        }

        return view('silverchannel.email_history.show', compact('emailLog'));
    }
}
