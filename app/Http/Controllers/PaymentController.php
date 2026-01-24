<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\IntegrationService;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $integrationService;

    public function __construct(PaymentService $paymentService, IntegrationService $integrationService)
    {
        $this->paymentService = $paymentService;
        $this->integrationService = $integrationService;
    }

    public function checkout(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['DRAFT', 'WAITING_PAYMENT'])) {
            return redirect()->route('silverchannel.orders.show', $order)
                ->with('error', 'Order cannot be paid.');
        }

        // Get Main Store Bank Details
        $mainStore = Store::whereHas('user.roles', function($q) {
            $q->where('name', 'SUPER_ADMIN');
        })->first();

        // Get bank details from IntegrationService (store settings)
        $banks = $this->integrationService->get('store_payment_banks', []);

        // Decode JSON if it's a string
        if (is_string($banks)) {
            $banks = json_decode($banks, true);
        }
        
        // Fallback to Main Store model if settings are empty (backward compatibility)
        if (empty($banks) && $mainStore && $mainStore->bank_details) {
            $banks = is_array($mainStore->bank_details) ? $mainStore->bank_details : json_decode($mainStore->bank_details, true);
        }

        // Ensure we have an array of banks
        if (!is_array($banks)) {
            $banks = [];
        }

        // Normalize bank data structure
        $banks = array_map(function($bank) {
            return [
                'bank_name' => $bank['bank_name'] ?? $bank['bank'] ?? 'Bank',
                'account_number' => $bank['account_number'] ?? $bank['number'] ?? '',
                'account_name' => $bank['account_name'] ?? $bank['name'] ?? '',
                'logo' => $bank['logo'] ?? null,
            ];
        }, $banks);

        // Calculate expiry time (default 60 minutes)
        $paymentTimeout = (int) $this->integrationService->get('store_payment_timeout', 60);
        $expiryTime = $order->created_at->addMinutes($paymentTimeout);

        return view('payment.checkout', compact('order', 'banks', 'expiryTime'));
    }

    public function process(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'payment_method' => 'required|in:manual,midtrans',
            'proof_file' => 'required_if:payment_method,manual|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $gateway = $this->paymentService->getGateway($request->payment_method);
            
            $payment = $gateway->charge($order, [
                'proof_file' => $request->file('proof_file'),
            ]);

            if ($request->payment_method === 'midtrans') {
                // Redirect to payment gateway
                return redirect($payment->payload['redirect_url']);
            }

            return redirect()->route('payment.success', $order);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Payment processing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Gunakan 'error' session flash agar muncul di alert merah di blade
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Optional: Check if payment is actually pending verification to prevent refreshing this page indefinitely
        // But for UX it might be okay to let them see it if status is WAITING_VERIFICATION
        
        return view('payment.success', compact('order'));
    }
}
