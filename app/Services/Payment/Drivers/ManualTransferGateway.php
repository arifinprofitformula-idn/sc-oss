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
                $directory = 'payment-proofs';
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                // Verify directory is writable (using local path check if possible)
                $storageRoot = config('filesystems.disks.public.root');
                $fullDirectoryPath = $storageRoot . DIRECTORY_SEPARATOR . $directory;
                
                if (file_exists($fullDirectoryPath) && !is_writable($fullDirectoryPath)) {
                    \Illuminate\Support\Facades\Log::error('Upload directory is not writable', [
                        'path' => $fullDirectoryPath
                    ]);
                    throw new \Exception("Direktori penyimpanan tidak dapat ditulis. Hubungi administrator.");
                }

                $filename = $file->hashName();
                if (empty($filename)) {
                    $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
                }
                
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

                $path = $directory . '/' . $filename;
                
                // Ensure path is not empty (defensive check)
                if (empty($path) || trim($path) === '') {
                    \Illuminate\Support\Facades\Log::critical('Generated file path is empty', [
                        'filename' => $filename,
                        'directory' => $directory
                    ]);
                    throw new \Exception("Internal Error: Generated file path is empty.");
                }

                \Illuminate\Support\Facades\Log::info('Attempting to store payment proof', [
                    'path' => $path,
                    'size' => strlen($fileContent),
                    'original_name' => $file->getClientOriginalName()
                ]);

                try {
                    $stored = Storage::disk('public')->put($path, $fileContent);
                } catch (\ValueError $e) {
                    // Catch specific PHP 8.0+ ValueError: Path must not be empty
                    \Illuminate\Support\Facades\Log::error('Storage::put failed with ValueError', [
                        'path' => $path,
                        'filename' => $filename,
                        'error' => $e->getMessage()
                    ]);
                    throw new \Exception("Gagal menyimpan file (ValueError): " . $e->getMessage());
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Storage::put failed with Exception', [
                        'path' => $path,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw new \Exception("Gagal menyimpan file (Storage Error): " . $e->getMessage());
                }
                
                if (!$stored) {
                    \Illuminate\Support\Facades\Log::error('Storage::put returned false', [
                        'path' => $path
                    ]);
                    throw new \Exception("Gagal menyimpan bukti pembayaran ke storage (Unknown Error).");
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
        $oldStatus = $order->status;
        $order->update(['status' => 'WAITING_VERIFICATION']);
        \App\Events\OrderStatusChanged::dispatch($order, $oldStatus, 'WAITING_VERIFICATION');

        return $payment;
    }

    public function handleCallback(Request $request)
    {
        // No callback for manual transfer
        return null;
    }
}
