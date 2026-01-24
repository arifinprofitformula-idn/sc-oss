<?php

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManualTransferGateway implements PaymentGatewayInterface
{
    public function charge(Order $order, array $data = []): Payment
    {
        // Handle file upload
        $path = null;
        if (isset($data['proof_file'])) {
            $file = $data['proof_file'];
            
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                // Ensure directory exists
                if (!Storage::disk('public')->exists('payment-proofs')) {
                    Storage::disk('public')->makeDirectory('payment-proofs');
                }

                $filename = $file->hashName();
                
                // Gunakan file_get_contents dan Storage::put untuk menghindari error "Path must not be empty" 
                // yang terjadi pada FilesystemAdapter::putFileAs di beberapa lingkungan Windows/Laragon
                
                // Coba dapatkan path yang valid
                $tempPath = $file->getRealPath();
                if (!$tempPath) {
                    // Fallback jika realpath gagal (misal di beberapa env Windows)
                    $tempPath = $file->getPathname();
                }

                if (!$tempPath || !file_exists($tempPath)) {
                    \Illuminate\Support\Facades\Log::error('Upload failed: temp file not found', [
                        'original_name' => $file->getClientOriginalName(),
                        'pathname' => $file->getPathname(),
                        'realpath' => $file->getRealPath(),
                        'valid' => $file->isValid(),
                        'error_code' => $file->getError()
                    ]);
                    throw new \Exception("Gagal membaca lokasi file sementara (Temp Path Issue). Silakan coba lagi.");
                }

                $fileContent = file_get_contents($tempPath);
                if ($fileContent === false) {
                    throw new \Exception("Gagal membaca isi file bukti pembayaran.");
                }

                $path = 'payment-proofs/' . $filename;
                $stored = Storage::disk('public')->put($path, $fileContent);
                
                if (!$stored) {
                    throw new \Exception("Gagal menyimpan bukti pembayaran ke storage.");
                }
            } elseif ($file instanceof \Illuminate\Http\UploadedFile && !$file->isValid()) {
                throw new \Exception("File bukti pembayaran tidak valid atau korup.");
            }
        }

        $paymentNumber = 'PAY-' . $order->order_number . '-' . Str::random(4);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_number' => $paymentNumber,
            'amount' => $order->total_amount,
            'method' => 'manual_transfer',
            'status' => 'PENDING_VERIFICATION', // Waiting for admin
            'proof_file' => $path,
            'paid_at' => now(), // User claims they paid now
        ]);

        // Update Order Status
        $order->update(['status' => 'WAITING_VERIFICATION']);

        return $payment;
    }

    public function handleCallback(Request $request)
    {
        // No callback for manual transfer
        return null;
    }
}
