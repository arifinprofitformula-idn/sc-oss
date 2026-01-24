<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

class SilverchannelPasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_can_update_silverchannel_password()
    {
        $admin = User::factory()->create(['status' => 'ACTIVE']);
        $admin->assignRole('SUPER_ADMIN');

        $silverchannel = User::factory()->create([
            'status' => 'ACTIVE', 
            'password' => Hash::make('oldpassword')
        ]);
        $silverchannel->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($admin)
            ->patch(route('admin.silverchannels.update-password', $silverchannel), [
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertRedirect(route('admin.silverchannels.index'));
        $response->assertSessionHas('success');

        $silverchannel->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $silverchannel->password));

        // Check Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'UPDATE_PASSWORD',
            'model_type' => User::class,
            'model_id' => $silverchannel->id,
        ]);
    }

    public function test_non_admin_cannot_update_password()
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);
        $user->assignRole('SILVERCHANNEL');

        $target = User::factory()->create(['status' => 'ACTIVE']);
        $target->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($user)
            ->patch(route('admin.silverchannels.update-password', $target), [
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertForbidden(); // Middleware role:SUPER_ADMIN
    }
    
    public function test_password_validation()
    {
        $admin = User::factory()->create(['status' => 'ACTIVE']);
        $admin->assignRole('SUPER_ADMIN');

        $silverchannel = User::factory()->create(['status' => 'ACTIVE']);
        $silverchannel->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($admin)
            ->patch(route('admin.silverchannels.update-password', $silverchannel), [
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_silver_channel_registration_controller_is_reachable()
    {
        // This tests the fix for BindingResolutionException
        $response = $this->get(route('register.silver'));
        $response->assertStatus(200);
    }
}
