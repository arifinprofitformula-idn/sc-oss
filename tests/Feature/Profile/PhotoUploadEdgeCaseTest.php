<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhotoUploadEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);
    }

    public function test_upload_file_with_empty_path_fails_gracefully()
    {
        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        // Simulate a file with empty path
        $file = new class(tempnam(sys_get_temp_dir(), 'test'), 'avatar.jpg', 'image/jpeg', null, true) extends UploadedFile {
            public function getRealPath(): string|false
            {
                return '';
            }
        };

        $response = $this->actingAs($user)->patch(route('profile.details.update'), [
            'gender' => 'Laki-laki',
            'job' => 'Dev',
            'religion' => 'Islam',
            'photo' => $file,
        ]);
        
        // Assert that we get a validation error instead of 500
        $response->assertSessionHasErrors(['photo']);
        
        // Assert the error message contains our custom message
        $errors = session('errors');
        $this->assertTrue(
            str_contains($errors->first('photo'), 'The uploaded file path is invalid') || 
            str_contains($errors->first('photo'), 'Path file tidak valid') ||
            str_contains($errors->first('photo'), 'invalid')
        );
    }

    public function test_photo_upload_fails_if_dimensions_too_small()
    {
        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        // 200x200 image (too small)
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($user)->patch(route('profile.details.update'), [
            'gender' => 'Laki-laki',
            'job' => 'Dev',
            'religion' => 'Islam',
            'photo' => $file,
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertStringContainsString('Ukuran gambar terlalu kecil', session('errors')->first('photo'));
    }

    public function test_photo_upload_fails_if_ratio_not_square()
    {
        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        // 300x400 image (valid size, invalid ratio)
        $file = UploadedFile::fake()->image('avatar.jpg', 300, 400);

        $response = $this->actingAs($user)->patch(route('profile.details.update'), [
            'gender' => 'Laki-laki',
            'job' => 'Dev',
            'religion' => 'Islam',
            'photo' => $file,
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertStringContainsString('Rasio gambar tidak memenuhi syarat', session('errors')->first('photo'));
    }

    public function test_photo_upload_fails_if_format_invalid()
    {
        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');
 
        // GIF file (image but not allowed mime)
        $file = UploadedFile::fake()->create('avatar.gif', 100, 'image/gif');
 
        $response = $this->actingAs($user)->patch(route('profile.details.update'), [
            'gender' => 'Laki-laki',
            'job' => 'Dev',
            'religion' => 'Islam',
            'bank_name' => 'BCA',
            'bank_account_no' => '123',
            'bank_account_name' => $user->name,
            'photo' => $file,
        ]);
 
        $response->assertSessionHasErrors(['photo']);
        $this->assertStringContainsString('Format file tidak didukung', session('errors')->first('photo'));
    }

    public function test_photo_upload_supports_webp_and_creates_backup_and_log()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        // Prepare existing photo to trigger backup creation
        $oldPath = 'user_profile_pictures/'.$user->id.'_old.jpg';
        Storage::disk('public')->put($oldPath, 'oldcontent');
        $user->profile_picture = $oldPath;
        $user->save();

        // WebP file (300x300)
        $file = UploadedFile::fake()->image('avatar.webp', 300, 300);

        $response = $this->actingAs($user)->postJson(route('profile.photo.update'), [
            'photo' => $file,
        ]);

        $response->assertStatus(200);
        
        $user->refresh();
        $path = $user->profile_picture;
        
        // 1. Verify stored in public
        Storage::disk('public')->assertExists($path);
        
        // 2. Verify backup created for old photo
        $this->assertTrue(
            collect(Storage::disk('public')->allFiles('backups/profile_pictures'))
                ->filter(fn($p) => str_contains($p, (string) $user->id.'_backup_'))
                ->isNotEmpty()
        );
        
        // 3. Verify Log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'UPDATE_PHOTO_AJAX',
            'model_type' => \App\Models\User::class,
        ]);
        
        // Verify Log Content (New Values)
        $log = \App\Models\AuditLog::where('user_id', $user->id)
                ->where('action', 'UPDATE_PHOTO_AJAX')
                ->latest()
                ->first();
                
        $this->assertNotNull($log);
        $this->assertEquals($path, $log->new_values['profile_picture']);
        $this->assertArrayHasKey('timestamp', $log->new_values);
    }
}
