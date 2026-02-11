<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ChatMessage;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    public function index()
    {
        return view('silverchannel.support.index');
    }

    public function getConversations(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        $status = $request->get('status'); // open, in_progress, resolved (mapped to order status or chat tags?)

        // Fetch orders that have messages OR are specifically requested
        // For now, let's list orders that have at least one message from either side
        // OR orders that are active.
        // Actually, "Chat Pengaduan" usually implies specific support tickets.
        // Since we are using Order-based chat, we list Orders.
        
        $query = Order::where('user_id', $user->id)
            ->whereHas('chatMessages') // Only show orders with chat history
            ->with(['latestChatMessage', 'items']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('items', function($iq) use ($search) {
                      $iq->where('product_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by chat status/priority if we had that column on Order (we added chat_priority, chat_tags)
        // We can use 'chat_priority' for filtering
        
        $orders = $query->orderByDesc(
            ChatMessage::select('created_at')
                ->whereColumn('order_id', 'orders.id')
                ->latest()
                ->take(1)
        )->paginate(20);

        // Transform for frontend
        $conversations = $orders->getCollection()->map(function ($order) {
            $latestMsg = $order->latestChatMessage;
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status, // Order status acting as ticket status for now
                'support_status' => $order->support_status ?? 'open',
                'chat_priority' => $order->chat_priority ?? 'medium',
                'last_message' => $latestMsg ? $latestMsg->message : '',
                'last_message_at' => $latestMsg ? $latestMsg->created_at->diffForHumans() : '',
                'last_message_raw' => $latestMsg ? $latestMsg->created_at : null,
                'unread_count' => $order->chatMessages()
                    ->where('sender_id', '!=', Auth::id())
                    ->where('is_read', false)
                    ->count(),
                'product_summary' => $order->items->take(1)->pluck('product_name')->implode(', ') . ($order->items->count() > 1 ? '...' : ''),
            ];
        });

        return response()->json([
            'data' => $conversations,
            'next_page_url' => $orders->nextPageUrl()
        ]);
    }

    public function getMessages(Request $request, Order $order)
    {
        // Authorize
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Mark as read only if we are fetching latest messages (not historical)
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

        // Get 50 messages, ordered by newest first
        $messages = $query->orderBy('created_at', 'desc')->take(50)->get();
        
        // Check if there are more messages (this is an approximation, robust way needs count or fetching 51)
        $hasMore = false;
        if ($messages->count() === 50) {
             $oldestId = $messages->last()->id;
             $hasMore = $order->chatMessages()->where('id', '<', $oldestId)->exists();
        }

        // Reverse to chronological order
        $messages = $messages->reverse()->values();

        $transformed = $messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'is_sender' => $msg->sender_id === Auth::id(),
                'sender_name' => $msg->sender->name,
                'created_at' => $msg->created_at->format('H:i'),
                'attachment_url' => $msg->attachment_path ? Storage::url($msg->attachment_path) : null,
                'is_read' => $msg->is_read,
            ];
        });

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'support_status' => $order->support_status ?? 'open',
                'chat_priority' => $order->chat_priority,
            ],
            'messages' => $transformed,
            'has_more' => $hasMore
        ]);
    }

    public function sendMessage(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->support_status === 'closed') {
            // Auto reopen ticket
            $oldStatus = $order->support_status;
            $order->support_status = 'reopened';
            $order->support_closed_at = null;
            $order->save();

            // Log history
            \App\Models\SupportStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => 'reopened',
                'comment' => 'Tiket dibuka kembali otomatis karena pesan baru dari pengguna.'
            ]);
        }

        $request->validate([
            'message' => 'required_without:attachment|string|nullable',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat-attachments', 'public');
        }

        $message = ChatMessage::create([
            'order_id' => $order->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'is_read' => false,
        ]);

        // Auto assign agent if not assigned
        if (!$order->chat_assigned_to) {
            // Find available agent: CUSTOMER_SERVICE > ADMIN > SUPER_ADMIN
            $agent = \App\Models\User::role('CUSTOMER_SERVICE')->inRandomOrder()->first();
            if (!$agent) {
                 $agent = \App\Models\User::role('ADMIN')->inRandomOrder()->first();
            }
            if (!$agent) {
                 $agent = \App\Models\User::role('SUPER_ADMIN')->inRandomOrder()->first();
            }

            if ($agent) {
                $order->update(['chat_assigned_to' => $agent->id]);
            }
        }

        // Touch order updated_at to bump priority if needed or trigger events
        $order->touch();

        // Broadcast the message
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Illuminate\Support\Facades\Log::error('Broadcasting failed for message ID ' . $message->id . ': ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'is_sender' => true,
                'sender_name' => Auth::user()->name,
                'created_at' => $message->created_at->format('H:i'),
                'attachment_url' => $message->attachment_path ? Storage::url($message->attachment_path) : null,
                'is_read' => false,
            ]
        ]);
    }
}
