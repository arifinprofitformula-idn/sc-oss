<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualPaymentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed Roles
        if (!\Spatie\Permission\Models\Role::where('name', 'SUPER_ADMIN')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'SUPER_ADMIN']);
        }
        if (!\Spatie\Permission\Models\Role::where('name', 'SILVERCHANNEL')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'SILVERCHANNEL']);
        }

        // Mock Store and Settings
        $store = Store::factory()->create();
        StoreSetting::create([
            'distributor_name' => 'Test Distributor',
            'distributor_address' => 'Test Address',
            'distributor_phone' => '081234567890',
            'province_id' => '1',
            'city_id' => '1',
            'subdistrict_id' => '1',
        ]);
    }

    /** @test */
    public function it_can_upload_payment_proof_with_robust_handling()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            // 'profile_completeness' => 100, // Removed because it's an accessor, not a column
            'address' => 'Test Address',
            'phone' => '081234567890',
            'nik' => '1234567890123456',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Test User',
        ]);
        
        // Mock profile completeness attribute by filling necessary fields in factory if needed
        // Or we can mock the middleware in the test if User model calculation is strict
        // But let's try to fill the user model first.
        $user->assignRole('SILVERCHANNEL');
        
        // Force update fields to ensure profile completeness > 70
        $user->update([
            'status' => 'ACTIVE', // Ensure user is approved
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08123456789',
            'nik' => '1234567890123456',
            'address' => 'Jalan Test',
            'province_id' => '11',
            'city_id' => '1101',
            'subdistrict_id' => '110101',
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'Single',
            'job' => 'Developer',
            'profile_picture' => 'profile.jpg',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Test User'
        ]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'WAITING_PAYMENT',
            'payment_method' => 'manual',
            'total_amount' => 150000,
        ]);

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($user)
            ->post(route('payment.process', $order), [
                'payment_method' => 'manual',
                'proof_file' => $file,
            ]);

        $response->assertRedirect(route('payment.success', $order));
        
        // Verify File Exists in Fake Storage
        $this->assertTrue(Storage::disk('public')->exists('payment-proofs/' . $file->hashName()));
        
        // Verify DB
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'manual_transfer',
            'status' => 'PENDING_VERIFICATION',
        ]);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'WAITING_VERIFICATION',
        ]);
    }
}
