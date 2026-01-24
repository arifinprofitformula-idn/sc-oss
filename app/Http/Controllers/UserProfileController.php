<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\ShippingService;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserProfileController extends Controller
{
    protected $shippingService;
    protected $integrationService;

    public function __construct(ShippingService $shippingService, IntegrationService $integrationService)
    {
        $this->shippingService = $shippingService;
        $this->integrationService = $integrationService;
    }

    /**
     * Handle partial profile photo update via AJAX.
     * Includes auto-compression and transactional safety.
     */
    public function updatePhoto(Request $request)
    {
        $user = $request->user();
        
        // Capture old values for audit
        $oldPath = $user->profile_picture;

        // Profiling Start
        $startTime = microtime(true);
        \Illuminate\Support\Facades\Log::info("ProfilePhotoUpdate: Started for user {$user->id}");

        // Check server limits manually to give better feedback
        $uploadMax = $this->parseSize(ini_get('upload_max_filesize'));
        $postMax = $this->parseSize(ini_get('post_max_size'));
        
        // Note: If file > post_max_size, $_FILES is empty.
        if (empty($_FILES) && empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $postMax) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran file melebihi batas server (Post Max Size: ' . ini_get('post_max_size') . ').'
            ], 422);
        }

        // 1. Validate
        $request->validate([
            'photo' => [
                'required', 
                'file', 
                'image', 
                'mimes:jpg,jpeg,png,webp', 
                'max:10240' // 10MB limit for upload
            ],
        ], [
            'photo.required' => 'Silakan pilih foto terlebih dahulu.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format file tidak didukung. Harap upload file JPG, JPEG, PNG, atau WebP.',
            'photo.max' => 'Ukuran file terlalu besar (Max 10MB).',
        ]);

        $file = $request->file('photo');
        
        // Check against upload_max_filesize specifically
        if ($file->getSize() > $uploadMax) {
             return response()->json([
                'success' => false,
                'message' => 'Ukuran file melebihi batas server (' . ini_get('upload_max_filesize') . ').'
            ], 422);
        }

        $path = null;
        $backupPath = null;
        \Illuminate\Support\Facades\Log::info("ProfilePhotoUpdate: Validation passed. Time: " . (microtime(true) - $startTime) . "s");

        try {
            // Backup Old Photo (Safety Measure)
            // We create a backup copy just in case, though we only delete the original at the very end.
            if (!empty($oldPath) && is_string($oldPath) && trim($oldPath) !== '' && Storage::disk('public')->exists($oldPath)) {
                $backupDir = 'backups/profile_pictures';
                if (!Storage::disk('public')->exists($backupDir)) {
                    Storage::disk('public')->makeDirectory($backupDir);
                }
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $backupPath = $backupDir . '/' . $user->id . '_backup_' . time() . '.' . $extension;
                Storage::disk('public')->copy($oldPath, $backupPath);
            }

            // 2. Compression Logic
            $imageContents = '';
            $extension = $file->getClientOriginalExtension();
            $targetExtension = 'jpg';
            
            $compressStart = microtime(true);

            // If > 1MB, compress
            if ($file->getSize() > 1024 * 1024) {
                $imageResource = imagecreatefromstring($file->get());
                if (!$imageResource) {
                    throw new \Exception('Gagal memproses gambar.');
                }

                // Get dimensions
                $width = imagesx($imageResource);
                $height = imagesy($imageResource);
                $maxDim = 1000;

                // Resize if too big (e.g. > 1000px)
                if ($width > $maxDim || $height > $maxDim) {
                    $ratio = $width / $height;
                    if ($ratio > 1) {
                        $newWidth = $maxDim;
                        $newHeight = $maxDim / $ratio;
                    } else {
                        $newHeight = $maxDim;
                        $newWidth = $maxDim * $ratio;
                    }
                    
                    $newImage = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
                    
                    // Handle transparency before converting to JPG (fill with white)
                    $white = imagecolorallocate($newImage, 255, 255, 255);
                    imagefill($newImage, 0, 0, $white);
                    
                    imagecopyresampled($newImage, $imageResource, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $width, $height);
                    $imageResource = $newImage;
                } else {
                    // If no resize needed, but we need to convert to JPG (handle transparency)
                    $newImage = imagecreatetruecolor($width, $height);
                    $white = imagecolorallocate($newImage, 255, 255, 255);
                    imagefill($newImage, 0, 0, $white);
                    imagecopy($newImage, $imageResource, 0, 0, 0, 0, $width, $height);
                    $imageResource = $newImage;
                }

                // Output to buffer as JPG
                ob_start();
                imagejpeg($imageResource, null, 80); // 80% quality
                $imageContents = ob_get_clean();
                imagedestroy($imageResource);
            } else {
                // If < 1MB, convert to JPG
                $imageResource = imagecreatefromstring($file->get());
                if ($imageResource) {
                    $width = imagesx($imageResource);
                    $height = imagesy($imageResource);
                    
                    $newImage = imagecreatetruecolor($width, $height);
                    $white = imagecolorallocate($newImage, 255, 255, 255);
                    imagefill($newImage, 0, 0, $white);
                    imagecopy($newImage, $imageResource, 0, 0, 0, 0, $width, $height);
                    
                    ob_start();
                    imagejpeg($newImage, null, 90); // High quality for small images
                    $imageContents = ob_get_clean();
                    imagedestroy($imageResource);
                    imagedestroy($newImage);
                } else {
                    $imageContents = $file->get();
                }
            }
            \Illuminate\Support\Facades\Log::info("ProfilePhotoUpdate: Compression finished. Time: " . (microtime(true) - $compressStart) . "s");

            // 3. Store New Photo
            $storeStart = microtime(true);
            $filename = 'user_profile_pictures/' . $user->id . '_' . time() . '.' . $targetExtension;
            
            if (!Storage::disk('public')->put($filename, $imageContents)) {
                 throw new \Exception('Gagal menyimpan file ke storage.');
            }
            $path = $filename;
            \Illuminate\Support\Facades\Log::info("ProfilePhotoUpdate: Storage finished. Time: " . (microtime(true) - $storeStart) . "s");

            // 4. Update DB (User table)
            $user->profile_picture = $path;
            $user->save();
            \Illuminate\Support\Facades\Log::info("ProfilePhotoUpdate: DB updated. Total Time: " . (microtime(true) - $startTime) . "s");

            // 5. Cleanup Old Photo & Backup
            if (!empty($oldPath) && is_string($oldPath) && trim($oldPath) !== '' && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            
            // Keep backup file for audit purposes
            
            // Audit Log
            try {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'UPDATE_PHOTO_AJAX',
                    'model_type' => User::class,
                    'model_id' => $user->id,
                    'old_values' => ['profile_picture' => $oldPath],
                    'new_values' => [
                        'profile_picture' => $path,
                        'compressed' => true, // Always processed
                        'timestamp' => now()->toDateTimeString(),
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                // Ignore audit log errors
            }

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui.',
                'url' => asset('storage/' . $path)
            ]);

        } catch (\Exception $e) {
            // Rollback: Delete new file if it was created
            if ($path && !empty($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            // Note: We do not restore from backup because we never deleted the original file 
            // until step 5 (which is after success). 
            // So the original file is still intact at $oldPath.
            // We just need to clean up the backup if it was created.
            if ($backupPath && Storage::disk('public')->exists($backupPath)) {
                Storage::disk('public')->delete($backupPath);
            }

            \Illuminate\Support\Facades\Log::error('Photo update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal memperbarui foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle standard profile update.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $section = $request->input('section');

        return DB::transaction(function () use ($request, $user, $section) {
            // Fallback validation for photo edge cases (legacy form submissions without AJAX)
            if ($request->hasFile('photo') || $request->input('photo')) {
                $messages = [
                    'photo.image' => 'File harus berupa gambar.',
                    'photo.mimes' => 'Format file tidak didukung',
                    'photo.min_dimensions' => 'Ukuran gambar terlalu kecil',
                    'photo.square' => 'Rasio gambar tidak memenuhi syarat',
                    'photo.path' => 'Path file tidak valid',
                ];

                $errors = [];
                $file = $request->file('photo');

                // Invalid path
                if ($file && (method_exists($file, 'getRealPath') && $file->getRealPath() === '')) {
                    $errors['photo'][] = $messages['photo.path'];
                }

                // Mime check
                if ($file && !in_array(strtolower($file->getClientOriginalExtension()), ['jpg','jpeg','png','webp'])) {
                    $errors['photo'][] = $messages['photo.mimes'];
                }

                // Dimension checks (min 300x300, require square)
                if ($file && $file->isValid()) {
                    $size = @getimagesize($file->getPathname());
                    if ($size && is_array($size)) {
                        [$width, $height] = [$size[0] ?? 0, $size[1] ?? 0];
                        if ($width < 300 || $height < 300) {
                            $errors['photo'][] = $messages['photo.min_dimensions'];
                        }
                        if ($width !== $height) {
                            $errors['photo'][] = $messages['photo.square'];
                        }
                    }
                }

                if (!empty($errors)) {
                    return redirect()->route('profile.edit')->withErrors($errors);
                }
            }

            if ($section === 'personal') {
                $validated = $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'nik' => ['required', 'numeric', 'digits:16', Rule::unique('users', 'nik')->ignore($user->id)],
                    'address' => ['required', 'string', 'min:10', 'max:255'],
                    'province_id' => ['nullable'],
                    'province_name' => ['nullable', 'string'],
                    'city_id' => ['nullable'],
                    'subdistrict_id' => ['nullable'],
                    'subdistrict_name' => ['nullable', 'string'],
                    'village_id' => ['nullable'],
                    'village_name' => ['nullable', 'string'],
                    'city_name' => ['required', 'string'],
                    'postal_code' => ['required', 'numeric', 'digits:5'],
                    'birth_place' => ['nullable', 'string', 'max:255'],
                    'birth_date' => ['nullable', 'date'],
                    'marital_status' => ['nullable', 'string', 'max:50'],
                    'gender' => ['required', 'in:Laki-laki,Perempuan'],
                    'job' => ['required', 'string', 'max:255'],
                    'religion' => ['required', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
                ], [
                    'nik.required' => 'NIK wajib diisi.',
                    'nik.numeric' => 'NIK harus berupa angka.',
                    'nik.digits' => 'NIK harus berjumlah tepat 16 digit.',
                    'nik.unique' => 'NIK ini sudah terdaftar dalam sistem.',
                    'address.min' => 'Alamat harus diisi lengkap minimal 10 karakter.',
                    'address.max' => 'Alamat terlalu panjang (maksimal 255 karakter).',
                    'postal_code.numeric' => 'Kode pos harus berupa angka.',
                    'postal_code.digits' => 'Kode pos harus berjumlah 5 digit.',
                ]);

                // Capture old values for audit
                $oldUserData = [
                    'name' => $user->name,
                    'nik' => $user->nik,
                    'address' => $user->address,
                    'city_name' => $user->city_name,
                    'postal_code' => $user->postal_code,
                    'subdistrict_name' => $user->subdistrict_name,
                    'village_name' => $user->village_name ?? null,
                    'birth_place' => $user->birth_place,
                    'birth_date' => $user->birth_date,
                    'marital_status' => $user->marital_status,
                    'gender' => $user->gender,
                    'job' => $user->job,
                    'religion' => $user->religion,
                    'address_provider' => $user->address_provider ?? null,
                ];

                // Update User Basic Info & Profile Fields
                $user->name = $validated['name'];
                $user->nik = $validated['nik'] ?? $user->nik;
                $user->address = $validated['address'];
                $user->city_name = $validated['city_name'];
                $user->postal_code = $validated['postal_code'];
                
                if (isset($validated['province_id'])) $user->province_id = $validated['province_id'];
                if (isset($validated['province_name'])) $user->province_name = $validated['province_name'];
                if (isset($validated['city_id'])) $user->city_id = $validated['city_id'];
                if (isset($validated['subdistrict_id'])) $user->subdistrict_id = $validated['subdistrict_id'];
                if (isset($validated['subdistrict_name'])) $user->subdistrict_name = $validated['subdistrict_name'];
                if (isset($validated['village_id'])) $user->village_id = $validated['village_id'];
                if (isset($validated['village_name'])) $user->village_name = $validated['village_name'];
                
                // Save current active provider
                $user->address_provider = $this->integrationService->get('shipping_provider', 'rajaongkir');

                $user->birth_place = $validated['birth_place'];
                $user->birth_date = $validated['birth_date'];
                $user->marital_status = $validated['marital_status'];
                $user->gender = $validated['gender'];
                $user->job = $validated['job'];
                $user->religion = $validated['religion'];

                $user->save();

                // Audit Log for User Data
                if ($user->wasChanged()) {
                    try {
                        AuditLog::create([
                            'user_id' => $user->id,
                            'action' => 'UPDATE_PERSONAL_DATA',
                            'model_type' => User::class,
                            'model_id' => $user->id,
                            'old_values' => $oldUserData,
                            'new_values' => $user->getChanges(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('AuditLog Error (Personal Data): ' . $e->getMessage());
                    }
                }

                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data pribadi berhasil disimpan.',
                        'profile_completeness' => $user->profile_completeness,
                    ]);
                }

                return redirect()->route('profile.edit')->with('status', 'profile-details-updated')->with('message', 'Data pribadi berhasil disimpan.');

            } elseif ($section === 'contact') {
                // Debug log
                \Illuminate\Support\Facades\Log::info('Profile Contact Update Request', $request->all());

                // Sanitize phone (remove non-digits)
                if ($request->has('phone')) {
                    $request->merge(['phone' => preg_replace('/\D/', '', $request->input('phone'))]);
                }
                
                // Sanitize bank_account_no (remove non-digits if user inputs dashes/spaces)
                if ($request->has('bank_account_no')) {
                     // Keep it as string but maybe clean it up? 
                     // Or just let it be string. User might want dashes.
                }

                // Custom validation for phone/whatsapp
                $phoneRules = ['required', 'numeric', 'digits_between:10,15'];
                // Only enforce unique if the phone number has changed
                if ($request->input('phone') !== $user->phone) {
                    $phoneRules[] = Rule::unique('users', 'phone')->ignore($user->id);
                }

                $validated = $request->validate([
                    'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                    'phone' => $phoneRules,
                    'social_facebook' => ['nullable', 'string', 'max:255'],
                    'social_instagram' => ['nullable', 'string', 'max:255'],
                    'social_tiktok' => ['nullable', 'string', 'max:255'],
                    'social_thread' => ['nullable', 'string', 'max:255'],
                    'bank_name' => ['nullable', 'string', 'max:255'],
                    'bank_account_no' => ['nullable', 'string', 'max:50'], // Changed from numeric to string
                    'bank_account_name' => ['nullable', 'string', 'max:255'],
                ], [
                    'phone.required' => 'Nomor WhatsApp wajib diisi.',
                    'phone.numeric' => 'Nomor WhatsApp harus berupa angka.',
                    'phone.digits_between' => 'Nomor WhatsApp harus antara 10-15 digit.',
                    'phone.unique' => 'Nomor WhatsApp ini sudah terdaftar.',
                    // 'bank_account_no.numeric' => 'Nomor Rekening harus berupa angka.', // Removed
                ]);

                $oldEmail = $user->email;
                $oldPhone = $user->phone;
                $oldSocialFacebook = $user->social_facebook;
                $oldSocialInstagram = $user->social_instagram;
                $oldSocialTiktok = $user->social_tiktok;
                $oldSocialThread = $user->social_thread;
                $oldBankName = $user->bank_name;
                $oldBankAccountNo = $user->bank_account_no;
                $oldBankAccountName = $user->bank_account_name;

                if ($user->email !== $validated['email']) {
                    $user->email = $validated['email'];
                    $user->email_verified_at = null;
                    $user->sendEmailVerificationNotification();
                }
                $user->phone = $validated['phone'];
                $user->social_facebook = $validated['social_facebook'] ?? null;
                $user->social_instagram = $validated['social_instagram'] ?? null;
                $user->social_tiktok = $validated['social_tiktok'] ?? null;
                $user->social_thread = $validated['social_thread'] ?? null;
                $user->bank_name = $validated['bank_name'] ?? null;
                $user->bank_account_no = $validated['bank_account_no'] ?? null;
                $user->bank_account_name = $validated['bank_account_name'] ?? null;
                
                $user->save();

                if ($user->wasChanged()) {
                    try {
                        AuditLog::create([
                            'user_id' => $user->id,
                            'action' => 'UPDATE_CONTACT_INFO',
                            'model_type' => User::class,
                            'model_id' => $user->id,
                            'old_values' => [
                                'email' => $oldEmail, 
                                'phone' => $oldPhone,
                                'social_facebook' => $oldSocialFacebook,
                                'social_instagram' => $oldSocialInstagram,
                                'social_tiktok' => $oldSocialTiktok,
                                'social_thread' => $oldSocialThread,
                                'bank_name' => $oldBankName,
                                'bank_account_no' => $oldBankAccountNo,
                                'bank_account_name' => $oldBankAccountName,
                            ],
                            'new_values' => $user->getChanges(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('AuditLog Error (Contact): ' . $e->getMessage());
                    }
                }

                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data kontak berhasil disimpan.',
                        'profile_completeness' => $user->profile_completeness,
                    ]);
                }

                return redirect()->route('profile.edit')->with('status', 'profile-details-updated')->with('message', 'Data kontak berhasil disimpan.');
            }

            return redirect()->route('profile.edit');
        });
    }

    /**
     * Location Data Endpoints
     */
    public function getProvinces()
    {
        try {
            $provinces = $this->shippingService->getProvinces();
            return response()->json($provinces);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCities($province)
    {
        try {
            $cities = $this->shippingService->getCities($province);
            return response()->json($cities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSubdistricts($city)
    {
        try {
            $subdistricts = $this->shippingService->getSubdistricts($city);
            return response()->json($subdistricts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getVillages($subdistrict)
    {
        try {
            $villages = $this->shippingService->getVillages($subdistrict);
            return response()->json($villages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Parse INI size string to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }
}
