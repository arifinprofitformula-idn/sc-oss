<?php

namespace Tests\Feature;

use App\Models\Payout;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Payout\PayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class FileUploadSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles
        if (!Role::where('name', 'SUPER_ADMIN')->exists()) {
            Role::create(['name' => 'SUPER_ADMIN']);
        }
        if (!Role::where('name', 'SILVERCHANNEL')->exists()) {
            Role::create(['name' => 'SILVERCHANNEL']);
        }
    }

    /** @test */
    public function payout_approval_uses_safe_file_storage()
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create();
        
        // Mock Payout Service or let it run if it doesn't depend on external APIs
        // Assuming Payout creation is simple enough
        $payout = Payout::create([
            'payout_number' => 'TEST-PO-001',
            'user_id' => $user->id,
            'amount' => 50000,
            'status' => 'REQUESTED',
        ]);

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($admin)
            ->patch(route('admin.payouts.approve', $payout), [
                'proof_file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check if file exists in the correct folder
        $this->assertTrue(Storage::disk('public')->exists('payout-proofs/' . $file->hashName()));
        
        // Verify DB update
        $payout->refresh();
        $this->assertEquals('payout-proofs/' . $file->hashName(), $payout->proof_file);
        $this->assertEquals('PROCESSED', $payout->status);
    }

    /** @test */
    public function silverchannel_profile_photo_update_uses_safe_storage()
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');
        
        // Ensure profile exists
        UserProfile::create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->actingAs($admin)
            ->put(route('admin.silverchannels.update', $user), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'photo' => $file,
                'city_id' => '1', // String required
                'city_name' => 'Jakarta',
                'province_id' => '1', // String required
                'province_name' => 'DKI',
                'whatsapp' => '08123456789', // Required
                'phone' => '08123456789',
                'referral_code' => $user->referral_code, // Might be required or unique check
                'silver_channel_id' => $user->silver_channel_id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check file existence
        $this->assertTrue(Storage::disk('public')->exists('profile-photos/' . $file->hashName()));

        // Verify DB
        $user->refresh();
        $this->assertEquals('profile-photos/' . $file->hashName(), $user->profile->photo_path);
    }
}
