<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\SupportStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $silverchannel;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        Role::create(['name' => 'SUPER_ADMIN']);
        Role::create(['name' => 'SILVERCHANNEL']);
        Role::create(['name' => 'ADMIN']);
        Role::create(['name' => 'CUSTOMER_SERVICE']);

        // Create Admin (Super Admin bypasses middleware usually, but let's be safe)
        $this->admin = User::factory()->create([
            'status' => 'ACTIVE',
        ]);
        $this->admin->assignRole('SUPER_ADMIN');

        // Create Silverchannel with complete profile to pass middleware
        $this->silverchannel = User::factory()->create([
            'status' => 'ACTIVE',
            'phone' => '081234567890',
            'nik' => '1234567890123456',
            'address' => 'Test Address',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'religion' => 'Islam',
            'marital_status' => 'single',
            'job' => 'Entrepreneur',
            'profile_picture' => 'default.jpg',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Test User',
        ]);
        $this->silverchannel->assignRole('SILVERCHANNEL');

        // Create Order (which acts as the support ticket context)
        $this->order = Order::factory()->create([
            'user_id' => $this->silverchannel->id,
            'status' => 'paid',
            'support_status' => 'open'
        ]);
    }

    /** @test */
    public function admin_can_update_support_status()
    {
        // Using patchJson because route is PATCH
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order), [
                'status' => 'on_progress'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'support_status' => 'on_progress'
        ]);

        $this->assertDatabaseHas('support_status_histories', [
            'order_id' => $this->order->id,
            'old_status' => 'open',
            'new_status' => 'on_progress',
            'user_id' => $this->admin->id
        ]);
    }

    /** @test */
    public function admin_must_provide_comment_when_closing_ticket()
    {
        // Attempt without comment
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order), [
                'status' => 'closed'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);

        // Attempt with comment
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order), [
                'status' => 'closed',
                'comment' => 'Issue resolved successfully.'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'support_status' => 'closed'
        ]);
        
        $this->assertNotNull($this->order->fresh()->support_closed_at);
    }

    /** @test */
    public function silverchannel_can_reopen_ticket_by_sending_message()
    {
        // Set status to closed
        $this->order->update(['support_status' => 'closed', 'support_closed_at' => now()]);

        $response = $this->actingAs($this->silverchannel)
            ->postJson(route('silverchannel.support.send', $this->order), [
                'message' => 'Hello, I still have issues.'
            ]);

        $response->assertStatus(200);
        
        $this->order->refresh();
        $this->assertEquals('reopened', $this->order->support_status);
        $this->assertNull($this->order->support_closed_at);
        
        $this->assertDatabaseHas('support_status_histories', [
            'order_id' => $this->order->id,
            'old_status' => 'closed',
            'new_status' => 'reopened',
            'user_id' => $this->silverchannel->id
        ]);
    }

    /** @test */
    public function silverchannel_can_send_message_when_ticket_is_open_or_reopened()
    {
        // 1. Open
        $this->order->update(['support_status' => 'open']);

        $response = $this->actingAs($this->silverchannel)
            ->postJson(route('silverchannel.support.send', $this->order), [
                'message' => 'I have an issue.'
            ]);

        $response->assertStatus(200);

        // 2. Reopened
        $this->order->update(['support_status' => 'reopened']);

        $response = $this->actingAs($this->silverchannel)
            ->postJson(route('silverchannel.support.send', $this->order), [
                'message' => 'It is happening again.'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_reopen_closed_ticket()
    {
        // First close it
        $this->order->update([
            'support_status' => 'closed',
            'support_closed_at' => now()
        ]);

        // Reopen
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order), [
                'status' => 'reopened'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'support_status' => 'reopened',
            'support_closed_at' => null
        ]);
    }
}
