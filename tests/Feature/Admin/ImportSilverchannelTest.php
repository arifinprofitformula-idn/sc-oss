<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportSilverchannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup roles
        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);
    }

    public function test_super_admin_can_access_import_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->get(route('admin.silverchannels.import'));
        $response->assertStatus(200);
        $response->assertSee('Upload File CSV');
    }

    public function test_import_button_is_visible_on_index_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->get(route('admin.silverchannels.index'));
        $response->assertStatus(200);
        $response->assertSee(route('admin.silverchannels.import'));
        $response->assertSee('Import Data');
    }

    public function test_import_creates_new_silverchannel()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $csvContent = "id_silverchannel,nama_channel,alamat,kota,kode_pos,telepon,email,tanggal_bergabung,status_aktif\n";
        $csvContent .= "SC001,Channel A,Jl. Contoh No.1,Jakarta,12345,0211234567,channelA@example.com,15-01-2023,true";

        $file = UploadedFile::fake()->createWithContent('silverchannels.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.silverchannels.import.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        
        $user = User::where('silver_channel_id', 'SC001')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Channel A', $user->name);
        $this->assertEquals('channelA@example.com', $user->email);
        $this->assertEquals('ACTIVE', $user->status);
        $this->assertEquals('12345', $user->postal_code);
        $this->assertTrue($user->hasRole('SILVERCHANNEL'));
    }

    public function test_import_updates_existing_silverchannel()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        // Create existing user
        $user = User::factory()->create([
            'silver_channel_id' => 'SC001',
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $csvContent = "id_silverchannel,nama_channel,alamat,kota,kode_pos,telepon,email,tanggal_bergabung,status_aktif\n";
        $csvContent .= "SC001,New Name,Jl. Baru,Bandung,54321,08123456789,new@example.com,20-02-2023,false";

        $file = UploadedFile::fake()->createWithContent('update.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.silverchannels.import.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('INACTIVE', $user->status);
        $this->assertEquals('54321', $user->postal_code);
    }

    public function test_import_validation_fails()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        // Invalid email, missing ID
        $csvContent = "id_silverchannel,nama_channel,alamat,kota,kode_pos,telepon,email,tanggal_bergabung,status_aktif\n";
        $csvContent .= ",Channel Invalid,Jl. A,Kota A,123,021,invalid-email,15-01-2023,true";

        $file = UploadedFile::fake()->createWithContent('invalid.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.silverchannels.import.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        
        $result = session('import_result');
        $this->assertEquals(0, $result['success_count']);
        $this->assertEquals(1, $result['failed_count']);
        $this->assertNotEmpty($result['errors']);
    }
}
