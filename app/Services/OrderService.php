<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderTrackingUpdated;
use App\Events\OrderStatusChanged;
use Exception;

class OrderService
{
    // Status Constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_WAITING_PAYMENT = 'WAITING_PAYMENT';
    const STATUS_WAITING_VERIFICATION = 'WAITING_VERIFICATION';
    const STATUS_PAID = 'PAID';
    const STATUS_PACKING = 'PACKING';
    const STATUS_SHIPPED = 'SHIPPED';
    const STATUS_DELIVERED = 'DELIVERED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_REFUNDED = 'REFUNDED';
    const STATUS_RETURN_REQUESTED = 'RETURN_REQUESTED';
    const STATUS_RETURNED = 'RETURNED';

    /**
     * Create a new order from user's cart
     */
    public function createFromCart(User $user, array $shippingData, $note = null)
    {
        return DB::transaction(function () use ($user, $shippingData, $note) {
            $cartItems = $user->cart()->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new Exception("Cart is empty");
            }

            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item->product->price * $item->quantity;
            }

            // Create Order
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => self::STATUS_WAITING_PAYMENT, // Skip DRAFT/SUBMITTED for simple flow, or start at SUBMITTED
                'shipping_address' => $shippingData['address'],
                'notes' => $note,
            ]);

            // Create Order Items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'total' => $item->product->price * $item->quantity,
                ]);
            }

            // Log Creation
            $this->logStatusChange($order, null, self::STATUS_WAITING_PAYMENT, 'Order created from cart', $user->id);

            // Clear Cart
            $user->cart()->delete();

            return $order;
        });
    }

    /**
     * Create Order from Checkout Data
     */
    public function createOrder(User $user, array $items, array $addressData, array $shippingData, string $paymentMethod, float $subtotal, float $shippingCost, float $taxAmount = 0, ?string $notes = null, int $uniqueCode = 0, ?string $expiresAt = null, float $insuranceAmount = 0): Order
    {
        return DB::transaction(function () use ($user, $items, $addressData, $shippingData, $paymentMethod, $subtotal, $shippingCost, $taxAmount, $notes, $uniqueCode, $expiresAt, $insuranceAmount) {
            
            // Construct full address string
            // Assuming addressData has: name, phone, address, province_name, city_name, subdistrict_name, postal_code
            $postalCode = $addressData['postal_code'] ?? '';
            $village = isset($addressData['village_name']) ? ", {$addressData['village_name']}" : '';
            $fullAddress = "{$addressData['address']}{$village}, {$addressData['subdistrict_name']}, {$addressData['city_name']}, {$addressData['province_name']} {$postalCode} (Tel: {$addressData['phone']})";

            // Determine initial status
            $status = ($paymentMethod === 'cod') ? self::STATUS_SUBMITTED : self::STATUS_WAITING_PAYMENT;

            $totalAmount = $subtotal + $shippingCost + $taxAmount + $insuranceAmount;
            if ($paymentMethod === 'transfer') {
                $totalAmount += $uniqueCode;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'order_key' => 'wc_order_' . Str::random(13),
                'status' => $status,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'insurance_amount' => $insuranceAmount,
                'unique_code' => ($paymentMethod === 'transfer') ? $uniqueCode : 0,
                'total_amount' => $totalAmount,
                'expires_at' => $expiresAt,
                'shipping_address' => $fullAddress,
                'shipping_courier' => $shippingData['courier'],
                'shipping_service' => $shippingData['service'],
                'notes' => $notes,
                'payment_method' => $paymentMethod,
                'payload' => [
                    'shipping_detail' => $shippingData,
                    'address_detail' => $addressData
                ]
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'] ?? 'Unknown Product', // Add product_name
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);

                // Deduct Stock
                $product = \App\Models\Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            // Log creation
            $this->logStatusChange($order, null, $status, 'Order created via checkout', $user->id);

            \App\Events\OrderCreated::dispatch($order);

            return $order;
        });
    }

    /**
     * Update order status with validation and logging
     */
    public function updateStatus(Order $order, string $newStatus, ?string $note = null, ?int $userId = null)
    {
        if ($order->status === $newStatus) {
            return $order;
        }

        // TODO: Add transition validation logic here if needed

        $oldStatus = $order->status;
        
        $order->status = $newStatus;
        if ($newStatus === self::STATUS_PAID) {
            $order->paid_at = now();
        }
        $order->save();

        $this->logStatusChange($order, $oldStatus, $newStatus, $note, $userId);

        // Fire OrderStatusChanged event
        OrderStatusChanged::dispatch($order, $oldStatus, $newStatus);

        return $order;
    }

    /**
     * Log status change
     */
    protected function logStatusChange(Order $order, ?string $fromStatus, string $toStatus, ?string $note, ?int $userId)
    {
        OrderLog::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }

    /**
     * Update tracking number and notify user
     */
    public function updateTrackingNumber(Order $order, string $trackingNumber, ?string $note = null, ?int $userId = null)
    {
        $oldTracking = $order->shipping_tracking_number;
        
        // Update Order
        $order->shipping_tracking_number = $trackingNumber;
        
        // Auto update status to SHIPPED if not already
        $oldStatus = $order->status;
        if ($order->status !== self::STATUS_SHIPPED && $order->status !== self::STATUS_DELIVERED) {
            $order->status = self::STATUS_SHIPPED;
        }
        
        $order->save();

        // Log Change
        $logNote = "Tracking number updated from '" . ($oldTracking ?? '-') . "' to '$trackingNumber'.";
        if ($note) {
            $logNote .= " Note: $note";
        }

        $this->logStatusChange($order, $oldStatus, $order->status, $logNote, $userId);

        // Notify User
        if ($oldTracking !== $trackingNumber) {
            try {
                $order->user->notify(new OrderTrackingUpdated($order, $trackingNumber, $order->shipping_courier ?? 'Unknown'));
            } catch (\Exception $e) {
                // Log error but don't fail the transaction/request
                \Illuminate\Support\Facades\Log::error('Failed to send tracking update notification: ' . $e->getMessage());
            }
        }

        return $order;
    }

    /**
     * Add internal note to order log
     */
    public function addNote(Order $order, string $note, int $userId)
    {
        OrderLog::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'from_status' => $order->status,
            'to_status' => $order->status, // Status doesn't change
            'note' => $note,
        ]);
        
        return $order;
    }
}
