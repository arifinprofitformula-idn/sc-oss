<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment request.
     *
     * @param Order $order
     * @param array $data Additional data (e.g., proof file, selected bank)
     * @return Payment
     */
    public function charge(Order $order, array $data = []): Payment;

    /**
     * Handle callback/webhook from payment provider.
     *
     * @param Request $request
     * @return mixed
     */
    public function handleCallback(Request $request);
}
