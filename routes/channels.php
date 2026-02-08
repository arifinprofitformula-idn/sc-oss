<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Order;

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = Order::findOrNew($orderId);
    return $user->id === $order->user_id || $user->hasRole(['SUPER_ADMIN', 'ADMIN', 'CUSTOMER_SERVICE']);
});
