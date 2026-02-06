<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order.user'])->latest();

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('export') && $request->export == 'csv') {
            return $this->exportCsv($query->get());
        }

        $payments = $query->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $payment->load('order.user', 'order.items');
        return view('admin.payments.show', compact('payment'));
    }

    private function exportCsv($payments)
    {
        $filename = "payments-" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        return response()->stream(function () use ($handle, $payments) {
            fputcsv($handle, ['Payment Number', 'Order Number', 'User', 'Amount', 'Method', 'Status', 'Date']);
            
            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->payment_number,
                    $payment->order->order_number,
                    $payment->order->user->name,
                    $payment->amount,
                    $payment->method,
                    $payment->status,
                    $payment->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function verify(Payment $payment)
    {
        if ($payment->status !== 'PENDING_VERIFICATION') {
            return back()->with('error', 'Payment is not pending verification.');
        }

        try {
            DB::transaction(function () use ($payment) {
                // Update Payment
                $payment->update([
                    'status' => 'PAID',
                    'paid_at' => now(),
                ]);

                // Update Order
                $order = $payment->order;
                $oldStatus = $order->status;
                $order->update(['status' => 'PAID']);

                // Log Order Change
                OrderLog::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'PAID',
                    'note' => 'Payment verified by Admin via Manual Transfer',
                    'changed_by' => auth()->id(),
                ]);
                
                // Dispatch OrderPaid Event for Commission
                \App\Events\OrderPaid::dispatch($order);

                // Dispatch OrderStatusChanged for other listeners (e.g. Silverchannel Status Activation)
                // This ensures the Silverchannel user is activated if they were WAITING_VERIFICATION
                \App\Events\OrderStatusChanged::dispatch($order, $oldStatus, 'PAID');
            });
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'payment_id' => $payment->id, 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal memverifikasi pembayaran: ' . $e->getMessage());
        }

        return back()->with('success', 'Payment verified successfully.');
    }

    public function reject(Request $request, Payment $payment)
    {
        if ($payment->status !== 'PENDING_VERIFICATION') {
            return back()->with('error', 'Payment is not pending verification.');
        }

        $request->validate(['reason' => 'required|string']);

        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'status' => 'FAILED',
                'payload' => array_merge($payment->payload ?? [], ['rejection_reason' => $request->reason]),
            ]);

            $order = $payment->order;
            // Optionally revert order status or keep it waiting?
            // Usually if payment fails, order stays in WAITING_PAYMENT or goes to CANCELLED?
            // Let's keep it in WAITING_PAYMENT so they can try again, or CANCELLED.
            // Requirement says "Mekanisme retry". So revert to WAITING_PAYMENT or DRAFT?
            // If they uploaded proof, they were in WAITING_VERIFICATION.
            // Let's revert to WAITING_PAYMENT (or DRAFT if logical).
            
            // For now, let's keep it simple: Revert to DRAFT so they can retry checkout.
            $oldStatus = $order->status;
            $order->update(['status' => 'DRAFT']);
             
             OrderLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'DRAFT',
                'note' => 'Payment rejected: ' . $request->reason,
                'changed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Payment rejected.');
    }
}
