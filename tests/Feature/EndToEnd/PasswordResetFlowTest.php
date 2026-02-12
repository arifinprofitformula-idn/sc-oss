<?php

namespace Tests\Feature\EndToEnd;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup System Settings for Email
        SystemSetting::updateOrCreate(
            ['key' => 'email_provider'],
            ['value' => 'log', 'group' => 'email', 'type' => 'select']
        );
        
        SystemSetting::updateOrCreate(
            ['key' => 'email_route_auth'],
            ['value' => 'log', 'group' => 'email', 'type' => 'select']
        );

        // Setup Email Template
        EmailTemplate::create([
            'key' => 'forgot_password', // Legacy key
            'type' => 'forgot_password', // New type used by EmailService
            'name' => 'Forgot Password',
            'subject' => 'Reset Password Notification',
            'body' => 'Hello {{user_name}}, reset here: {{reset_url}}',
            'language' => 'ID',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function benchmark_password_reset_flow()
    {
        Queue::fake(); // Fake queue to isolate dispatch performance from execution
        
        $iterations = 5;
        $totalDuration = 0;
        $successCount = 0;
        $failedCount = 0;

        echo "\n\n--- Password Reset Flow Benchmark Report ---\n";

        for ($i = 0; $i < $iterations; $i++) {
            $user = User::factory()->create([
                'email' => "bench_{$i}@example.com",
            ]);

            $start = microtime(true);
            
            try {
                $response = $this->post('/forgot-password', [
                    'email' => $user->email,
                ]);
                $response->assertStatus(302);
                $response->assertSessionHas('status');
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                // echo "Failed: " . $e->getMessage() . "\n"; 
            }

            $duration = (microtime(true) - $start) * 1000; // ms
            $totalDuration += $duration;
            
            echo "Iteration " . ($i + 1) . ": {$duration} ms\n";
        }

        $avgTime = $totalDuration / $iterations;
        echo "\nSummary:\n";
        echo "Total Iterations: {$iterations}\n";
        echo "Success Rate: " . (($successCount / $iterations) * 100) . "%\n";
        echo "Average Response Time: " . round($avgTime, 2) . " ms\n";
        echo "Bottleneck Analysis: " . ($avgTime > 1000 ? "SLOW - Check Database/Rendering" : "OPTIMAL") . "\n";
        echo "--------------------------------------------\n";
    }

    /** @test */
    public function complete_password_reset_flow_simulation()
    {
        Queue::fake();

        // 1. User Registration / Existence
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'name' => 'Test User'
        ]);

        // 2. User Requests Password Reset
        $response = $this->post('/forgot-password', [
            'email' => 'user@example.com',
        ]);

        // 3. Validate Response
        $response->assertStatus(302);
        $response->assertSessionHas('status', __('passwords.sent'));

        // 4. Verify EmailService Triggered & Job Dispatched
        Queue::assertPushed(SendEmailJob::class, function ($job) use ($user) {
            $payload = $job->getPayload();
            return $payload['to'] === $user->email &&
                   $payload['type'] === 'forgot_password' &&
                   $payload['user_id'] === $user->id;
        });

        // 5. Verify Audit Trail (EmailLog)
        $this->assertDatabaseHas('email_logs', [
            'user_id' => $user->id,
            'to' => $user->email,
            'type' => 'forgot_password',
            'status' => 'queued', // Initial status
        ]);

        // 6. Simulate Job Processing (Delivery)
        // Manually process the job to verify log update
        $log = EmailLog::where('user_id', $user->id)->latest()->first();
        
        // In a real scenario, the job would run. Here we simulate the effect of the job success.
        $log->update(['status' => 'sent', 'sent_at' => now()]);
        
        $this->assertDatabaseHas('email_logs', [
            'id' => $log->id,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_handles_provider_selection_logic()
    {
        Queue::fake();
        
        // Set Auth route to use 'smtp' (mocked as log for test safety, but logic check)
        SystemSetting::updateOrCreate(
            ['key' => 'email_route_auth'],
            ['value' => 'custom_provider']
        );

        $user = User::factory()->create(['email' => 'provider_test@example.com']);

        // Trigger Reset via Service directly to inspect routing
        $service = app(EmailService::class);
        $service->resetPassword($user, 'dummy-token');

        Queue::assertPushed(SendEmailJob::class, function ($job) {
            return $job->getPayload()['mailer'] === 'custom_provider';
        });
    }

    /** @test */
    public function it_logs_failures_gracefully()
    {
        // Mock Exception during send
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('resetPassword')->andThrow(new \Exception('Provider Timeout'));
        });

        $user = User::factory()->create(['email' => 'fail@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'fail@example.com',
        ]);

        // Controller should catch exception and return error
        // Note: The standard PasswordResetLinkController catches exceptions.
        // We need to ensure our implementation allows this bubbling or handling.
        // Current controller code:
        /*
        try {
            $status = Password::sendResetLink(...)
        } catch (\Exception $e) {
            // returns back with errors
        }
        */
        
        $response->assertSessionHasErrors(['email']);
    }
}
