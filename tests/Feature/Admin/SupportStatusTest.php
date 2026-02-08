<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\SupportStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SupportStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $silverchannel;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        $adminRole = Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $scRole = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
        
        // Setup Users
            $this->admin = User::factory()->create([
                'status' => 'active',
                // Fill required fields for profile completeness > 70%
                'phone' => '081' . rand(10000000, 99999999),
                'nik' => (string) rand(1000000000000000, 9999999999999999),
                'address' => 'Jl. Admin No. 1',
                'province_id' => 1,
                'city_id' => 1,
                'subdistrict_id' => 1,
                'postal_code' => '12345',
                'birth_place' => 'Jakarta',
                'birth_date' => '1990-01-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'marital_status' => 'single',
                'job' => 'Admin',
                'bank_name' => 'BCA',
                'bank_account_no' => (string) rand(1000000000, 9999999999),
                'bank_account_name' => 'Admin User'
            ]);
            $this->admin->assignRole('SUPER_ADMIN');

            $this->silverchannel = User::factory()->create([
                'status' => 'active',
                // Fill required fields for profile completeness > 70%
                'phone' => '082' . rand(10000000, 99999999),
                'nik' => (string) rand(1000000000000000, 9999999999999999),
                'address' => 'Jl. SC No. 2',
                'province_id' => 1,
                'city_id' => 1,
                'subdistrict_id' => 1,
                'postal_code' => '12345',
                'birth_place' => 'Bandung',
                'birth_date' => '1995-01-01',
                'gender' => 'female',
                'religion' => 'Islam',
                'marital_status' => 'married',
                'job' => 'Reseller',
                'bank_name' => 'Mandiri',
                'bank_account_no' => (string) rand(1000000000, 9999999999),
                'bank_account_name' => 'SC User'
            ]);
            $this->silverchannel->assignRole('SILVERCHANNEL');

        // Setup Order (as Chat Ticket)
        $this->order = Order::factory()->create([
            'user_id' => $this->silverchannel->id,
            'support_status' => 'open'
        ]);
    }

    /** @test */
    public function admin_can_update_support_status()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order->id), [
                'status' => 'on_progress'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'support_status' => 'on_progress'
        ]);
    }

    /** @test */
    public function admin_must_provide_comment_when_closing_status()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order->id), [
                'status' => 'closed'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
            
        // Valid request
        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order->id), [
                'status' => 'closed',
                'comment' => 'Issue resolved via phone'
            ]);
            
        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'support_status' => 'closed'
        ]);
        $this->assertNotNull($this->order->fresh()->support_closed_at);
    }

    /** @test */
    public function status_change_is_logged_in_history()
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.chats.status', $this->order->id), [
                'status' => 'on_progress'
            ]);

        $this->assertDatabaseHas('support_status_histories', [
            'order_id' => $this->order->id,
            'old_status' => 'open',
            'new_status' => 'on_progress',
            'user_id' => $this->admin->id
        ]);
    }

    /** @test */
    public function silverchannel_cannot_update_status()
    {
        $response = $this->actingAs($this->silverchannel)
            ->patchJson(route('admin.chats.status', $this->order->id), [
                'status' => 'closed',
                'comment' => 'I close this'
            ]);

        // Assuming middleware blocks non-admin from /admin routes
        $response->assertStatus(403); 
    }

    /** @test */
    public function silverchannel_cannot_send_message_when_ticket_is_closed()
    {
        // Close the ticket first
        $this->order->update(['support_status' => 'closed', 'support_closed_at' => now()]);

        $response = $this->actingAs($this->silverchannel)
            ->postJson(route('silverchannel.support.send', $this->order->id), [
                'message' => 'Hello?'
            ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Tiket ini sudah ditutup. Anda tidak dapat mengirim pesan lagi.']);
    }
}
