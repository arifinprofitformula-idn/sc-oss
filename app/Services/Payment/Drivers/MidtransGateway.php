<?php

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MidtransGateway implements PaymentGatewayInterface
{
    public function charge(Order $order, array $data = []): Payment
    {
        $paymentNumber = 'PAY-' . $order->order_number . '-' . Str::random(4);

        // Mock Midtrans Snap Token creation
        // In real impl, we would call Midtrans API here
        $snapToken = 'mock-snap-token-' . Str::random(20);
        $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken;

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_number' => $paymentNumber,
            'amount' => $order->total_amount,
            'method' => 'midtrans',
            'status' => 'PENDING',
            'external_id' => $snapToken,
            'payload' => [
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl
            ],
        ]);

        return $payment;
    }

    public function handleCallback(Request $request)
    {
        // Mock handling Midtrans Notification
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        
        // Find payment by order_id or payment_number logic
        // For now return dummy
        return ['status' => 'success'];
    }
}
