<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class SilverchannelProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup roles
        Role::create(['name' => 'SUPER_ADMIN']);
        Role::create(['name' => 'SILVERCHANNEL']);
    }

    public function test_super_admin_can_update_silverchannel_password_via_profile_update()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        $newPassword = 'NewPassword123!';

        $response = $this->actingAs($admin)->put(route('admin.silverchannels.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp' => '08123456789',
            'province_id' => '1',
            'province_name' => 'Test Prov',
            'city_id' => '1',
            'city_name' => 'Test City',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect(route('admin.silverchannels.index'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    public function test_password_validation_rules()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($admin)->put(route('admin.silverchannels.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp' => '08123456789',
            'province_id' => '1',
            'province_name' => 'Test Prov',
            'city_id' => '1',
            'city_name' => 'Test City',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_confirmation_required()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($admin)->put(route('admin.silverchannels.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp' => '08123456789',
            'province_id' => '1',
            'province_name' => 'Test Prov',
            'city_id' => '1',
            'city_name' => 'Test City',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_leave_password_blank_does_not_change_it()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!')
        ]);
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($admin)->put(route('admin.silverchannels.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp' => '08123456789',
            'province_id' => '1',
            'province_name' => 'Test Prov',
            'city_id' => '1',
            'city_name' => 'Test City',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('admin.silverchannels.index'));
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }
}
