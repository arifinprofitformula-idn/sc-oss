<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeliveryProofUploadTest extends TestCase
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
    }

    /** @test */
    public function it_can_upload_delivery_proof_to_delivered_disk_robustly()
    {
        Storage::fake('delivered');

        $user = User::factory()->create([
            'status' => 'ACTIVE',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'nik' => '1234567890123456',
            'address' => 'Test Address',
            'province_id' => '1',
            'city_id' => '1',
            'subdistrict_id' => '1',
            'postal_code' => '12345',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Test User',
            'job' => 'Tester', // Add job to increase completeness
            'birth_place' => 'Test City',
            'birth_date' => '1990-01-01',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderService::STATUS_SHIPPED,
            'total_amount' => 150000,
        ]);

        $file = UploadedFile::fake()->image('delivery.jpg');

        $response = $this->actingAs($user)
            ->post(route('silverchannel.orders.mark-delivered', $order), [
                'proof_of_delivery' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Verify File Exists in Fake Storage 'delivered' disk
        $this->assertTrue(Storage::disk('delivered')->exists($file->hashName()));
        
        // Verify DB
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderService::STATUS_DELIVERED,
            'proof_of_delivery' => $file->hashName(),
        ]);
    }
}
