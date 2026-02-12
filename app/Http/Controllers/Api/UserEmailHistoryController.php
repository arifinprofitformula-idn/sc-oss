<?php
declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Auth;

class UserEmailHistoryController extends Controller
{
    public function index()
    {
        $logs = EmailLog::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return response()->json($logs);
    }

    public function show(int $id)
    {
        $log = EmailLog::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($log);
    }
}

