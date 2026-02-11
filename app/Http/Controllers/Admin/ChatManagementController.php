<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\SupportStatusHistory;
use App\Models\QuickReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatManagementController extends Controller
{
    public function index()
    {
        // Get all agents (Super Admin, Admin, Customer Service)
        $agents = User::role(['SUPER_ADMIN', 'ADMIN', 'CUSTOMER_SERVICE'])->get(['id', 'name']);
        return view('admin.chats.index', compact('agents'));
    }

    public function getConversations(Request $request)
    {
        $query = Order::query()
            ->with(['user:id,name,email,silver_channel_id', 'chatAssignee:id,name'])
            ->withCount(['chatMessages as unread_count' => function ($q) {
                $q->where('is_read', false)->where('sender_id', '!=', Auth::id());
            }])
            ->whereHas('chatMessages');

        // Filter by Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Status
        if ($request->status) {
            if ($request->status === 'unread') {
                $query->having('unread_count', '>', 0);
            } elseif ($request->status === 'closed') {
                $query->where('support_status', 'closed');
            } else {
                $query->where('support_status', $request->status);
            }
        } else {
            // Default: Hide closed unless searched. Handle NULL safely.
            if (!$request->search) {
                $query->where(function($q) {
                    $q->where('support_status', '!=', 'closed')
                      ->orWhereNull('support_status');
                });
            }
        }

        // Filter by Priority
        if ($request->priority) {
            $query->where('chat_priority', $request->priority);
        }

        // Filter by Assignment
        if ($request->assigned_to) {
            if ($request->assigned_to === 'me') {
                $query->where('chat_assigned_to', Auth::id());
            } else {
                $query->where('chat_assigned_to', $request->assigned_to);
            }
        }

        // Sort by Unread Messages (Unread first)
        $query->orderBy('unread_count', 'desc');

        // Sort by Priority (Urgent > High > Medium > Low)
        $query->orderByRaw("FIELD(chat_priority, 'urgent', 'high', 'medium', 'low') DESC");
        
        // Then sort by latest activity (Newest updated_at first)
        $query->orderBy('updated_at', 'desc');
        
        // Pagination
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $conversations = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => $conversations->items(),
            'current_page' => $conversations->currentPage(),
            'last_page' => $conversations->lastPage(),
            'total' => $conversations->total(),
        ]);
    }

    public function getMessages(Request $request, Order $order)
    {
        // Mark as read
        if (!$request->has('before_id')) {
            ChatMessage::where('order_id', $order->id)
                ->where('sender_id', '!=', Auth::id())
                ->update(['is_read' => true]);
        }

        $query = $order->chatMessages()->with('sender:id,name');

        if ($request->has('before_id')) {
            $query->where('id', '<', $request->before_id);
        }
        
        if ($request->has('after_id')) {
            $query->where('id', '>', $request->after_id);
        }

        $messages = $query->orderBy('created_at', 'desc')->take(50)->get();
        
        $hasMore = false;
        if ($messages->count() === 50) {
             $oldestId = $messages->last()->id;
             $hasMore = $order->chatMessages()->where('id', '<', $oldestId)->exists();
        }

        // Reverse to show oldest first
        $messages = $messages->reverse()->values();

        return response()->json([
            'order' => $order->load(['user', 'items', 'chatAssignee', 'supportStatusHistories.user']),
            'messages' => $messages,
            'has_more' => $hasMore
        ]);
    }

    public function sendMessage(Request $request, Order $order)
    {
        $request->validate(['message' => 'required|string']);

        $message = ChatMessage::create([
            'order_id' => $order->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        return response()->json($message->load('sender'));
    }

    public function assignChat(Request $request, Order $order)
    {
        $order->update(['chat_assigned_to' => $request->user_id]);
        return response()->json(['success' => true]);
    }

    public function updatePriority(Request $request, Order $order)
    {
        $order->update(['chat_priority' => $request->priority]);
        return response()->json(['success' => true]);
    }

    public function updateTags(Request $request, Order $order)
    {
        $order->update(['chat_tags' => $request->tags]); // Expecting array
        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:open,pending,on_progress,escalated,resolved,closed,reopened',
            'comment' => 'required_if:status,closed|string|nullable'
        ]);

        $oldStatus = $order->support_status ?? 'open';
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true]);
        }

        $order->support_status = $newStatus;
        if ($newStatus === 'closed') {
            $order->support_closed_at = now();
        } else {
            $order->support_closed_at = null; // Reopen or other status
        }
        $order->save();

        SupportStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'comment' => $request->comment
        ]);

        return response()->json(['success' => true]);
    }

    public function getQuickReplies()
    {
        return response()->json(QuickReply::where('is_active', true)->get());
    }

    public function storeQuickReply(Request $request)
    {
        $request->validate(['title' => 'required', 'message' => 'required']);
        QuickReply::create([
            'title' => $request->title,
            'message' => $request->message,
            'created_by' => Auth::id()
        ]);
        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        // Count unread messages where sender is NOT an admin/agent
        $count = ChatMessage::whereHas('sender', function($q) {
             $q->whereDoesntHave('roles', function($r) {
                 $r->whereIn('name', ['SUPER_ADMIN', 'ADMIN', 'CUSTOMER_SERVICE']);
             });
        })->where('is_read', false)->count();

        return response()->json(['count' => $count]);
    }

    public function getStats()
    {
        $totalChats = Order::whereHas('chatMessages')->count();
        $unreadChats = Order::whereHas('chatMessages', function($q) {
             $q->where('is_read', false)->where('sender_id', '!=', Auth::id());
        })->count();
        
        $priorityCounts = Order::whereHas('chatMessages')
            ->select('chat_priority', DB::raw('count(*) as count'))
            ->groupBy('chat_priority')
            ->pluck('count', 'chat_priority')
            ->toArray();

        return response()->json([
            'total' => $totalChats,
            'unread' => $unreadChats,
            'priority' => $priorityCounts
        ]);
    }

    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="chat_history.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Order', 'Sender', 'Message', 'Is Read']);

            ChatMessage::with(['order', 'sender'])->chunk(1000, function ($messages) use ($handle) {
                foreach ($messages as $msg) {
                    fputcsv($handle, [
                        $msg->created_at,
                        $msg->order->order_number ?? 'N/A',
                        $msg->sender->name ?? 'Unknown',
                        $msg->message,
                        $msg->is_read ? 'Yes' : 'No'
                    ]);
                }
            });
            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
