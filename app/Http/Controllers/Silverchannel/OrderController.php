<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = \App\Models\Order::where('user_id', Auth::id())
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(10);
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

    public function markAsDelivered(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== OrderService::STATUS_SHIPPED) {
             return back()->with('error', 'Order must be in SHIPPED status to be marked as delivered.');
        }

        $request->validate([
            'proof_of_delivery' => 'required|mimes:jpg,jpeg,png,bmp,gif,svg,webp,pdf|max:5120', // 5MB, allow images and PDF
        ]);

        try {
            DB::transaction(function () use ($request, $order) {
                // Robust file upload handling
                $file = $request->file('proof_of_delivery');
                // Removed 'proofs' directory to match requested structure: storage/app/public/delivered/{filename}
                
                // Ensure directory exists on 'delivered' disk
                // Note: 'delivered' disk root is now storage_path('app/public/delivered')
                // We don't need a subdirectory if we want it directly in .../delivered
                
                $filename = $file->hashName();
                if (empty($filename)) {
                    $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
                }

                // Get valid temp path
                $tempPath = $file->getRealPath() ?: $file->getPathname();
                if (!$tempPath || !file_exists($tempPath)) {
                    throw new \Exception("Gagal membaca lokasi file sementara. Silakan coba lagi.");
                }

                $fileContent = file_get_contents($tempPath);
                if ($fileContent === false) {
                    throw new \Exception("Gagal membaca isi file bukti pengiriman.");
                }

                // Path relative to disk root
                $path = $filename;
                
                if (empty($path) || trim($path) === '') {
                    throw new \Exception("Internal Error: Generated file path is empty.");
                }

                try {
                    // Store to 'delivered' disk
                    $stored = Storage::disk('delivered')->put($path, $fileContent);
                } catch (\ValueError $e) {
                    \Illuminate\Support\Facades\Log::error('Storage::put failed (delivered disk)', [
                        'path' => $path,
                        'error' => $e->getMessage()
                    ]);
                    throw new \Exception("Gagal menyimpan file: " . $e->getMessage());
                }

                if (!$stored) {
                    throw new \Exception("Gagal menyimpan bukti pengiriman ke storage.");
                }
                
                $order->update(['proof_of_delivery' => $path]);
                
                $this->orderService->updateStatus(
                    $order, 
                    OrderService::STATUS_DELIVERED, 
                    'Order received by Silverchannel. Proof uploaded.', 
                    Auth::id()
                );
            });

            return back()->with('success', 'Order marked as delivered successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
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
