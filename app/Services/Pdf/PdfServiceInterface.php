<?php
declare(strict_types=1);

namespace App\Services\Pdf;

use App\Models\Order;
use App\Models\Payout;

interface PdfServiceInterface
{
    /**
     * Generate base64 attachment for order invoice.
     * Return shape: ['base64' => string, 'name' => 'invoice-XXXX.pdf'] or null on failure.
     */
    public function generateOrderInvoice(Order $order): ?array;

    /**
     * Generate base64 attachment for payout receipt.
     * Return shape: ['base64' => string, 'name' => 'payout-XXXX.pdf'] or null on failure.
     */
    public function generatePayoutReceipt(Payout $payout): ?array;
}

