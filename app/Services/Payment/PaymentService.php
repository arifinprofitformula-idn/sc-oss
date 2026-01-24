<?php

namespace App\Services\Payment;

use App\Services\Payment\Drivers\ManualTransferGateway;
use App\Services\Payment\Drivers\MidtransGateway;
use InvalidArgumentException;

class PaymentService
{
    public function getGateway(string $driver): PaymentGatewayInterface
    {
        return match ($driver) {
            'manual' => new ManualTransferGateway(),
            'midtrans' => new MidtransGateway(),
            // 'xendit' => new XenditGateway(),
            default => throw new InvalidArgumentException("Unsupported payment driver: {$driver}"),
        };
    }
}
