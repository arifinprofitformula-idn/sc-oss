<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.email_logs.index', [
            'filters' => [
                'from' => $request->query('from'),
                'to' => $request->query('to'),
                'email' => $request->query('email'),
                'status' => $request->query('status'),
                'autorefresh' => $request->boolean('autorefresh', true),
            ],
        ]);
    }

    public function list(Request $request)
    {
        $query = EmailLog::query();

        if ($email = $request->query('email')) {
            $query->where('to', 'like', '%' . $email . '%');
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->query('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->where('created_at', '<=', $to);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
