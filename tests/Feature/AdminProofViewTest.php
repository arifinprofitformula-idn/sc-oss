<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProofViewTest extends TestCase
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
    public function silverchannel_can_upload_pdf_proof()
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
            'job' => 'Tester',
            'birth_place' => 'Test City',
            'birth_date' => '1990-01-01',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderService::STATUS_SHIPPED,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('silverchannel.orders.mark-delivered', $order), [
                'proof_of_delivery' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertTrue(Storage::disk('delivered')->exists($file->hashName()));
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderService::STATUS_DELIVERED,
            'proof_of_delivery' => $file->hashName(),
        ]);
    }

    /** @test */
    public function admin_can_view_order_with_pdf_proof_and_see_correct_button_label()
    {
        Storage::fake('delivered');

        // Create Admin
        $admin = User::factory()->create(['status' => 'ACTIVE', 'email' => 'admin@epi.com']);
        $admin->assignRole('SUPER_ADMIN');

        // Create Order with PDF proof
        $proofPath = 'test-proof.pdf';
        // We just need the path string in DB, file existence in fake storage is optional for view rendering unless it checks size/mime
        // But the view uses Storage::disk('delivered')->url()
        
        $order = Order::factory()->create([
            'proof_of_delivery' => $proofPath,
            'status' => OrderService::STATUS_DELIVERED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        
        // Check for new button label
        $response->assertSee('Lihat Bukti Terima');
        
        // Check for AlpineJS logic for PDF
        $response->assertSee("proofType = 'pdf'", false);
        
        // Check for iframe presence in template
        $response->assertSee('<iframe :src="proofUrl"', false);
    }

    /** @test */
    public function admin_can_view_order_with_image_proof()
    {
        Storage::fake('delivered');

        // Create Admin
        $admin = User::factory()->create(['status' => 'ACTIVE', 'email' => 'admin2@epi.com']);
        $admin->assignRole('SUPER_ADMIN');

        // Create Order with Image proof
        $proofPath = 'test-proof.jpg';
        
        $order = Order::factory()->create([
            'proof_of_delivery' => $proofPath,
            'status' => OrderService::STATUS_DELIVERED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        
        // Check for image logic
        $response->assertSee("proofType = 'image'", false);
        
        // Check for img tag
        $response->assertSee('<img :src="proofUrl"', false);
    }
}
