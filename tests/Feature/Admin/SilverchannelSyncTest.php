<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SilverchannelSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::create(['name' => 'SUPER_ADMIN']);
        Role::create(['name' => 'SILVERCHANNEL']);
    }

    public function test_silverchannel_registration_syncs_id_and_referral_code()
    {
        $response = $this->post(route('register.silver.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'nik' => '1234567890123456',
            'whatsapp' => '+6281234567890',
            'province_id' => '1',
            'province_name' => 'Test Province',
            'city_id' => '1',
            'city_name' => 'Test City',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->silver_channel_id);
        $this->assertNotNull($user->referral_code);
        $this->assertEquals($user->silver_channel_id, $user->referral_code);
        $this->assertStringStartsWith('EPISCTE', $user->silver_channel_id); // EPISC + TE (Test)
    }

    public function test_admin_create_silverchannel_syncs_id_and_referral_code()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->post(route('admin.silverchannels.store'), [
            'name' => 'Admin Created',
            'email' => 'admincreated@example.com',
            'whatsapp' => '08123456789',
            'province_id' => '1',
            'province_name' => 'Test Province',
            'city_id' => '1',
            'city_name' => 'Test City',
        ]);

        $user = User::where('email', 'admincreated@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($user->silver_channel_id, $user->referral_code);
        $this->assertStringStartsWith('EPISCAD', $user->silver_channel_id); // EPISC + AD (Admin)
    }

    public function test_admin_approve_silverchannel_syncs_id_and_referral_code()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create([
            'status' => 'PENDING_REVIEW',
            'silver_channel_id' => null,
            'referral_code' => null,
            'name' => 'Pending User'
        ]);
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($admin)->post(route('admin.silverchannels.approve', $user));

        $user->refresh();
        $this->assertEquals('ACTIVE', $user->status);
        $this->assertNotNull($user->silver_channel_id);
        $this->assertNotNull($user->referral_code);
        $this->assertEquals($user->silver_channel_id, $user->referral_code);
        $this->assertStringStartsWith('EPISCPE', $user->silver_channel_id); // EPISC + PE (Pending)
    }
}
