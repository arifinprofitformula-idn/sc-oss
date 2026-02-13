<?php

namespace Tests\Feature;

use App\Events\OrderStatusChanged;
use App\Listeners\SendInvoicePaidToCustomer;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\User;
use App\Services\Email\EmailService;
use App\Services\Pdf\PdfServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_log_created_on_order_paid()
    {
        Queue::fake();
        
        // Ensure factories work
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'WAITING_PAYMENT',
            'payment_method' => 'transfer',
        ]);

        // Mock PdfService to avoid PDF generation issues
        $this->mock(PdfServiceInterface::class, function ($mock) {
            $mock->shouldReceive('generateOrderInvoice')->andReturn(null);
        });

        // Resolve EmailService (real one, not mocked, so it writes to DB)
        $emailService = app(EmailService::class);
        
        // Instantiate Listener
        $listener = new SendInvoicePaidToCustomer($emailService, app(PdfServiceInterface::class));

        // Create Event
        $event = new OrderStatusChanged($order, 'WAITING_PAYMENT', 'PAID');

        // Execute Handle
        $listener->handle($event);

        // Assert Log Created
        $this->assertDatabaseHas('email_logs', [
            'user_id' => $user->id,
            'type' => 'order_invoice_paid',
            'related_type' => Order::class,
            'related_id' => $order->id,
            'status' => 'queued',
        ]);

        // Assert Job Pushed
        Queue::assertPushed(\App\Jobs\SendEmailJob::class);
    }
}
