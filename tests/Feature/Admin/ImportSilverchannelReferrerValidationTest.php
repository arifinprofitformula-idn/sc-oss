<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportSilverchannelReferrerValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup roles
        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('SUPER_ADMIN');
    }

    /** @test */
    public function it_fails_import_if_referrer_id_is_missing_in_csv_row()
    {
        // Header sesuai getHeaders di service
        $header = "id_silverchannel,nama_channel,email,telepon,nik,alamat,kota,kode_pos,tempat_lahir,tanggal_lahir,jenis_kelamin,agama,status_perkawinan,pekerjaan,tanggal_bergabung,status_aktif,referrer_id";
        
        // Row 1: Valid Referrer (misal kita buat dummy referrer dulu)
        $referrer = User::factory()->create(['silver_channel_id' => 'REF001']);
        
        // Row 2: Referrer Kosong
        $csvContent = $header . "\n";
        $csvContent .= "SC001,User Satu,u1@ex.com,081,123,Addr,City,123,P,1990-01-01,L,I,S,W,2024-01-01,1,REF001\n";
        $csvContent .= "SC002,User Dua,u2@ex.com,082,124,Addr,City,123,P,1990-01-01,L,I,S,W,2024-01-01,1,"; // Referrer Kosong

        $file = UploadedFile::fake()->createWithContent('missing_referrer.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.silverchannels.import.store'), [
            'file' => $file,
        ]);

        // Expect redirect back with errors
        $response->assertSessionHas('error');
        
        // Assert NO data inserted for SC001 (All-or-Nothing)
        $this->assertDatabaseMissing('users', ['silver_channel_id' => 'SC001']);
        $this->assertDatabaseMissing('users', ['silver_channel_id' => 'SC002']);
    }

    /** @test */
    public function it_fails_import_if_referrer_id_does_not_exist_in_database()
    {
        $header = "id_silverchannel,nama_channel,email,telepon,nik,alamat,kota,kode_pos,tempat_lahir,tanggal_lahir,jenis_kelamin,agama,status_perkawinan,pekerjaan,tanggal_bergabung,status_aktif,referrer_id";
        
        // Row 1: Referrer Invalid (Tidak ada di DB)
        $csvContent = $header . "\n";
        $csvContent .= "SC001,User Satu,u1@ex.com,081,123,Addr,City,123,P,1990-01-01,L,I,S,W,2024-01-01,1,INVALID_REF\n";

        $file = UploadedFile::fake()->createWithContent('invalid_referrer.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.silverchannels.import.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('error');
        // Assert session has specific error message about validation failure
        
        $this->assertDatabaseMissing('users', ['silver_channel_id' => 'SC001']);
    }

    /** @test */
    public function it_succeeds_import_if_all_referrers_are_valid()
    {
        $referrer = User::factory()->create([
            'silver_channel_id' => 'REF001',
            'referral_code' => 'REF001'
        ]);

        $header = "id_silverchannel,nama_channel,email,telepon,nik,alamat,kota,kode_pos,tempat_lahir,tanggal_lahir,jenis_kelamin,agama,status_perkawinan,pekerjaan,tanggal_bergabung,status_aktif,referrer_id";
        
        $csvContent = $header . "\n";
        $csvContent .= "SC001,User Satu,u1@ex.com,081,123,Addr,City,123,P,1990-01-01,L,I,S,W,2024-01-01,1,REF001\n";
        $csvContent .= "SC002,User Dua,u2@ex.com,082,124,Addr,City,123,P,1990-01-01,L,I,S,W,2024-01-01,1,REF001\n";

        $file = UploadedFile::fake()->createWithContent('success.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.silverchannels.import.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', ['silver_channel_id' => 'SC001', 'referrer_id' => $referrer->id]);
        $this->assertDatabaseHas('users', ['silver_channel_id' => 'SC002', 'referrer_id' => $referrer->id]);
    }

    /** @test */
    public function it_can_resolve_referrer_by_referral_code_if_silver_channel_id_does_not_match()
    {
        // Kadang referrer_id di CSV mungkin diisi referral_code user, bukan silver_channel_id-nya (meski biasanya sama)
        // Service kita handle keduanya.
        $referrer = User::factory()->create([
            'silver_channel_id' => 'SC_REF', 
            'referral_code' => 'KODE_REF'
        ]);

        $header = "id_silverchannel,nama_channel,email,telepon,nik,alamat,kota,kode_pos,tempat_lahir,tanggal_lahir,jenis_kelamin,agama,status_perkawinan,pekerjaan,tanggal_bergabung,status_aktif,referrer_id";
        
        $csvContent = $header . "\n";
        $csvContent .= "SC001,User Satu,u1@ex.com,081,123,Addr,City,123,P,1990-01-01,L,I,S,W,2024-01-01,1,KODE_REF\n";

        $file = UploadedFile::fake()->createWithContent('referral_code.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.silverchannels.import.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['silver_channel_id' => 'SC001', 'referrer_id' => $referrer->id]);
    }
}
