<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return view('silverchannel.orders.chat', compact('order'));
    }

    public function getMessages(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark unread messages from admin as read
        ChatMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $query = ChatMessage::where('order_id', $order->id)->with('sender');

        if ($request->has('after_id')) {
            $messages = $query->where('id', '>', $request->after_id)
                ->orderBy('created_at', 'asc')
                ->get();
            return response()->json(['data' => $messages]);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(50);

        // Reverse collection to show oldest first in UI
        $response = $messages->toArray();
        $response['data'] = array_reverse($response['data']);

        return response()->json($response);
    }

    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required_without:attachment|nullable|string',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat-attachments', 'public');
        }

        $message = ChatMessage::create([
            'order_id' => $order->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'attachment_path' => $path,
        ]);

        return response()->json($message->load('sender'));
    }
}
