<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = Auth::user()->orders()->orderBy('created_at', 'desc')->paginate(10);
        return view('silverchannel.orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $order = $this->orderService->createFromCart(
                Auth::user(),
                ['address' => $request->shipping_address],
                $request->notes
            );

            return redirect()->route('silverchannel.orders.show', $order)
                             ->with('success', 'Order created successfully. Please proceed to payment.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product', 'logs');
        return view('silverchannel.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== OrderService::STATUS_WAITING_PAYMENT && $order->status !== OrderService::STATUS_DRAFT && $order->status !== OrderService::STATUS_SUBMITTED) {
            return back()->with('error', 'Order cannot be cancelled in current status.');
        }

        $this->orderService->updateStatus($order, OrderService::STATUS_CANCELLED, 'Cancelled by user', Auth::id());

        return back()->with('success', 'Order cancelled successfully.');
    }
}
