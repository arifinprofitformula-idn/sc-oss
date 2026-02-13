<?php
declare(strict_types=1);

namespace App\Services\Pdf;

use App\Models\Order;
use App\Models\Payout;
use Illuminate\Support\Facades\View;

/**
 * Lightweight HTML->PDF generator with graceful fallback.
 * If no PDF engine is available, it returns a base64-encoded HTML file as attachment.
 */
class HtmlToPdfService implements PdfServiceInterface
{
    public function generateOrderInvoice(Order $order): ?array
    {
        $html = View::make('pdf.invoice', compact('order'))->render();
        $filename = 'invoice-' . ($order->order_number ?? $order->id) . '.pdf';
        $path = storage_path('app/tmp/invoices/' . $filename);
        $binary = $this->htmlToPdf($html) ?? $this->htmlToAttachment($html);
        if (!$binary) {
            return null;
        }
        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0775, true);
        }
        @file_put_contents($path, $binary);
        return [
            'path' => $path,
            'name' => $filename,
            'base64' => base64_encode($binary),
        ];
    }

    public function generatePayoutReceipt(Payout $payout): ?array
    {
        $html = View::make('pdf.payout-receipt', compact('payout'))->render();
        $filename = 'payout-' . ($payout->payout_number ?? $payout->id) . '.pdf';
        $path = storage_path('app/tmp/invoices/' . $filename);
        $binary = $this->htmlToPdf($html) ?? $this->htmlToAttachment($html);
        if (!$binary) {
            return null;
        }
        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0775, true);
        }
        @file_put_contents($path, $binary);
        return [
            'path' => $path,
            'name' => $filename,
            'base64' => base64_encode($binary),
        ];
    }

    /**
     * Try to convert HTML to PDF using available engines (placeholder).
     * This stub returns null; you can integrate barryvdh/laravel-dompdf later.
     */
    protected function htmlToPdf(string $html): ?string
    {
        $class = 'Dompdf\\Dompdf';
        if (class_exists($class)) {
            $dompdf = new $class();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }
        return null;
    }

    /**
     * Fallback: wrap HTML in minimal header and return as binary; mail client will still accept attachment.
     */
    protected function htmlToAttachment(string $html): ?string
    {
        $content = "<!DOCTYPE html><html><head><meta charset=\"utf-8\"></head><body>" . $html . "</body></html>";
        return $content;
    }
}
