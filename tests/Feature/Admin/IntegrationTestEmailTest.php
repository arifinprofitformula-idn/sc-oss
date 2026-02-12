<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendEmailJob;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class IntegrationTestEmailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Role
        if (!Role::where('name', 'SUPER_ADMIN')->exists()) {
            Role::create(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole('SUPER_ADMIN');
    }

    public function test_admin_can_send_test_email()
    {
        Bus::fake();

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.integrations.email.test'), [
                'email' => 'test@example.com',
                'subject' => 'Test Subject',
                'message' => 'Test Message',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        Bus::assertDispatched(SendEmailJob::class, function ($job) {
            return $job->getPayload()['to'] === 'test@example.com' &&
                   $job->getPayload()['type'] === 'test_email';
        });
        
        $this->assertDatabaseHas('email_logs', [
            'to' => 'test@example.com',
            'subject' => 'Test Subject',
            'type' => 'test_email',
        ]);
    }

    public function test_validation_rules()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('admin.integrations.email.test'), [
                'email' => 'not-an-email',
                'subject' => '',
                'message' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'subject', 'message']);
    }

    public function test_rate_limiting()
    {
        Bus::fake();

        // Send 5 requests (allowed)
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->user)
                ->postJson(route('admin.integrations.email.test'), [
                    'email' => "test{$i}@example.com",
                    'subject' => 'Test',
                    'message' => 'Test',
                ])->assertStatus(200);
        }

        // 6th request should fail
        $this->actingAs($this->user)
            ->postJson(route('admin.integrations.email.test'), [
                'email' => 'test6@example.com',
                'subject' => 'Test',
                'message' => 'Test',
            ])->assertStatus(429); // Too Many Requests
    }
}
