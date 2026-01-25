<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IntegrationService;
use App\Services\RajaOngkirService;
use App\Models\Store;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\StoreContact;
use App\Models\StoreOperatingHour;

class GlobalStoreSettingController extends Controller
{
    protected $integrationService;
    protected $rajaOngkirService;

    public function __construct(IntegrationService $integrationService, RajaOngkirService $rajaOngkirService)
    {
        $this->integrationService = $integrationService;
        $this->rajaOngkirService = $rajaOngkirService;
    }

    public function edit($tab = null)
    {
        $settings = [
            'silverchannel_store_menu_active' => $this->integrationService->get('silverchannel_store_menu_active'),
            'store_payment_unique_code_active' => $this->integrationService->get('store_payment_unique_code_active', 0),
            'store_payment_timeout' => $this->integrationService->get('store_payment_timeout', 60),
        ];

        $user = Auth::user();
        $store = Store::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'EPI Center', 'slug' => 'epi-center']
        );

        // Pastikan store ini menjadi store utama untuk status operasional Silverchannel
        $this->integrationService->set(
            'silverchannel_primary_store_id',
            $store->id,
            'system',
            'integer'
        );

        $provinces = [];
        try {
            $provinces = $this->rajaOngkirService->getProvinces();
        } catch (\Exception $e) {
            // Handle error gracefully
        }

        $validTabs = ['identity','contact','hours','payment'];
        if (!in_array($tab, $validTabs)) {
            $tab = 'identity';
        }

        // Prepare Operating Hours
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $defaultOperatingHours = [];
        foreach ($days as $day) {
            $defaultOperatingHours[$day] = [
                'open' => '09:00',
                'close' => '17:00',
                'is_closed' => ($day === 'sunday')
            ];
        }
        $currentHours = $store->operating_hours ?? [];
        $operatingHours = is_array($currentHours) ? array_merge($defaultOperatingHours, $currentHours) : $defaultOperatingHours;

        $bankDetails = $store->bank_details ?? [];

        return view('admin.settings.store', compact(
            'settings', 'store', 'provinces', 'tab', 
            'days', 'operatingHours', 'bankDetails'
        ));
    }

    public function updatePayment(Request $request)
    {
        $request->validate([
            'payment_methods' => 'nullable|array',
            'bank_details' => 'nullable|array',
            'bank_details.*.bank' => 'required|string',
            'bank_details.*.number' => 'required|string',
            'bank_details.*.name' => 'required|string',
            'bank_details.*.logo' => 'nullable|string', // URL/path to logo
            'unique_code_active' => 'nullable|boolean',
            'payment_timeout' => 'nullable|integer|min:1',
        ]);

        $store = Store::where('user_id', Auth::id())->firstOrFail();

        // Update Unique Code Setting
        $this->integrationService->set(
            'store_payment_unique_code_active',
            $request->boolean('unique_code_active') ? 1 : 0,
            'store',
            'boolean',
            'Activate unique code for transactions'
        );

        // Update Payment Timeout Setting
        $this->integrationService->set(
            'store_payment_timeout',
            $request->input('payment_timeout', 60),
            'store',
            'integer',
            'Payment timeout in minutes'
        );

        $store->update([
            'payment_methods' => $request->input('payment_methods', []),
            'bank_details' => $request->input('bank_details', []),
        ]);

        return response()->json(['success' => true]);
    }

    public function uploadBankLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            try {
                $file = $request->file('logo');
                
                if (!$file->isValid()) {
                    throw new \Exception('File upload failed or invalid file.');
                }

                // Ensure directory exists
                if (!Storage::disk('public')->exists('stores/banks')) {
                    Storage::disk('public')->makeDirectory('stores/banks');
                }

                $filename = 'bank-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Use file_get_contents and Storage::put to avoid "Path must not be empty" error
                // consistent with ManualTransferGateway implementation
                
                // Try to get valid path
                $tempPath = $file->getRealPath();
                if (!$tempPath) {
                    // Fallback if realpath fails
                    $tempPath = $file->getPathname();
                }

                if (!$tempPath || !file_exists($tempPath)) {
                    Log::error('Upload failed: temp file not found', [
                        'original_name' => $file->getClientOriginalName(),
                        'pathname' => $file->getPathname(),
                        'realpath' => $file->getRealPath(),
                    ]);
                    throw new \Exception("Gagal membaca lokasi file sementara (Temp Path Issue).");
                }

                $fileContent = file_get_contents($tempPath);
                if ($fileContent === false) {
                    throw new \Exception("Gagal membaca isi file logo.");
                }

                $path = 'stores/banks/' . $filename;
                $stored = Storage::disk('public')->put($path, $fileContent);
                
                if (!$stored) {
                    throw new \Exception('Failed to save file to storage.');
                }

                return response()->json([
                    'success' => true,
                    'path' => $path,
                    'url' => Storage::url($path)
                ]);
            } catch (\Throwable $e) {
                Log::error('Bank Logo Upload Error: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    public function update(Request $request)
    {
        // 1. Toggle Logic
        $isActive = $request->has('silverchannel_store_menu_active') ? 1 : 0;
        
        $this->integrationService->set(
            'silverchannel_store_menu_active',
            $isActive,
            'system',
            'boolean'
        );

        // 2. Store Settings Logic
        $user = Auth::user();
        $store = Store::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'province_id' => 'required',
            'city_id' => 'required',
            'subdistrict_id' => 'required',
            'postal_code' => 'required|string|max:10',
            'address' => 'required|string',
            
            // Arrays
            'social_links' => 'nullable|array',
            'operating_hours' => 'nullable|array',
            'couriers' => 'nullable|array',
        ]);

        $data = $request->except(['logo', '_token', '_method', 'silverchannel_store_menu_active']);
        
        if ($request->hasFile('logo')) {
            try {
                $oldPath = $store->logo_path;
                if (is_string($oldPath) && trim($oldPath) !== '' && Storage::disk('public')->exists($oldPath)) {
                    Log::info('Deleting old store logo', ['path' => $oldPath, 'store_id' => $store->id]);
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file('logo')->store('stores/logos', 'public');
                Log::info('Uploaded new store logo', ['path' => $path, 'store_id' => $store->id]);
                $data['logo_path'] = $path;
            } catch (\Throwable $e) {
                Log::error('Filesystem error on store logo update', [
                    'message' => $e->getMessage(),
                    'store_id' => $store->id,
                ]);
                return back()->withErrors(['logo' => 'Gagal memproses logo: ' . $e->getMessage()]);
            }
        }

        // Handle Slug update if name changed
        if ($request->name !== $store->name) {
            $data['slug'] = Str::slug($request->name);
        }

        $oldValues = $store->toArray();
        try {
            $store->update($data);
        } catch (\Throwable $e) {
            Log::error('Failed updating store settings', [
                'store_id' => $store->id,
                'data' => $data,
                'message' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Gagal menyimpan pengaturan: ' . $e->getMessage()]);
        }
        $newValues = $store->getChanges();

        if (!empty($newValues)) {
            AuditLog::log('update_global_store_settings', $store, $oldValues, $newValues);
        }

        return back()->with('success', 'Pengaturan menu toko berhasil diperbarui.');
    }

    public function updateToggle(Request $request)
    {
        $request->validate([
            'silverchannel_store_menu_active' => 'required|boolean',
        ]);

        $value = $request->boolean('silverchannel_store_menu_active') ? 1 : 0;

        $this->integrationService->set(
            'silverchannel_store_menu_active',
            $value,
            'system',
            'boolean'
        );

        return response()->json(['success' => true]);
    }

    public function updateIdentity(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_open' => 'nullable|boolean',
        ]);

        $store = Store::where('user_id', Auth::id())->firstOrFail();
        $data = $request->only(['name', 'description']);

        // Handle Logo Upload with Robust Logic (matching Profile Photo flow)
        if ($request->hasFile('logo')) {
            try {
                $file = $request->file('logo');
                
                if (!$file->isValid()) {
                    throw new \Exception('File logo tidak valid.');
                }

                // 1. Prepare Directory
                if (!Storage::disk('public')->exists('stores/logos')) {
                    Storage::disk('public')->makeDirectory('stores/logos');
                }

                // 2. Get File Content (Manual Read to avoid "Path must not be empty" error)
                $tempPath = $file->getRealPath();
                if (!$tempPath) {
                    $tempPath = $file->getPathname();
                }

                if (!$tempPath || !file_exists($tempPath)) {
                    throw new \Exception("Gagal membaca lokasi file sementara.");
                }

                // 3. Compression / Standardization Logic (optional but recommended)
                // Using simple logic here: if image resource can be created, re-save as optimized JPG
                // Otherwise just use raw content
                $imageContents = '';
                $extension = $file->getClientOriginalExtension();
                $targetExtension = 'jpg'; // Standardize to JPG for consistency

                // Basic compression if GD is available and file is image
                if (extension_loaded('gd') && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp'])) {
                     try {
                        $imageResource = imagecreatefromstring(file_get_contents($tempPath));
                        if ($imageResource) {
                            $width = imagesx($imageResource);
                            $height = imagesy($imageResource);
                            $maxDim = 1000;

                            // Resize logic
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
                            } else {
                                $newWidth = $width;
                                $newHeight = $height;
                                $newImage = imagecreatetruecolor($width, $height);
                            }

                            // Handle transparency
                            $white = imagecolorallocate($newImage, 255, 255, 255);
                            imagefill($newImage, 0, 0, $white);
                            imagecopyresampled($newImage, $imageResource, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $width, $height);

                            // Output
                            ob_start();
                            imagejpeg($newImage, null, 85);
                            $imageContents = ob_get_clean();
                            
                            imagedestroy($imageResource);
                            imagedestroy($newImage);
                        } else {
                            $imageContents = file_get_contents($tempPath);
                            $targetExtension = $extension;
                        }
                     } catch (\Throwable $e) {
                         // Fallback to raw content if GD fails
                         $imageContents = file_get_contents($tempPath);
                         $targetExtension = $extension;
                     }
                } else {
                    $imageContents = file_get_contents($tempPath);
                    $targetExtension = $extension;
                }

                // 4. Save New File
                $filename = 'store_' . $store->id . '_' . time() . '.' . $targetExtension;
                $path = 'stores/logos/' . $filename;

                if (!Storage::disk('public')->put($path, $imageContents)) {
                    throw new \Exception("Gagal menyimpan file ke storage.");
                }

                // 5. Delete Old Logo
                $oldPath = $store->logo_path;
                if (is_string($oldPath) && trim($oldPath) !== '' && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                $data['logo_path'] = $path;

            } catch (\Throwable $e) {
                Log::error('Store Logo Update Error: ' . $e->getMessage());
                return response()->json(['error' => 'Gagal upload logo: ' . $e->getMessage()], 422);
            }
        }

        if ($request->filled('is_open')) {
            $data['is_open'] = (bool) $request->boolean('is_open');
        }

        $store->update($data);

        try {
            app(\App\Services\StoreOperationalService::class)->refreshStatus();
        } catch (\Throwable $e) {
            Log::error('Failed refreshing store operational status after identity update', [
                'store_id' => $store->id,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Identitas toko berhasil diperbarui.',
            'logo_url' => isset($data['logo_path']) ? Storage::url($data['logo_path']) : null
        ]);
    }

    public function updateContact(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'province_id' => 'required|string',
            'city_id' => 'required|string',
            'subdistrict_id' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'social_links' => 'nullable|array',
        ]);

        $store = Store::where('user_id', Auth::id())->firstOrFail();
        $payload = $request->only(['address','province_id','city_id','subdistrict_id','postal_code','phone','whatsapp','email']);
        if ($request->has('social_links')) {
            $payload['social_links'] = $request->input('social_links');
        }

        // Update Store model directly as view relies on it
        $store->update($payload);

        // Also update StoreContact for redundancy/normalization if needed
        StoreContact::updateOrCreate(['store_id' => $store->id], array_merge(['store_id' => $store->id], $payload));
        
        return response()->json(['success' => true]);
    }

    public function updateHours(Request $request)
    {
        $request->validate([
            'operating_hours' => 'required|array',
            'is_open' => 'nullable|boolean',
        ]);

        $store = Store::where('user_id', Auth::id())->firstOrFail();
        
        if ($request->has('is_open')) {
            $store->update(['is_open' => $request->boolean('is_open')]);
        }

        $hours = $request->input('operating_hours');

        DB::transaction(function () use ($store, $hours) {
            StoreOperatingHour::where('store_id', $store->id)->delete();
            foreach ($hours as $day => $cfg) {
                StoreOperatingHour::create([
                    'store_id' => $store->id,
                    'day' => $day,
                    'open' => $cfg['open'] ?? null,
                    'close' => $cfg['close'] ?? null,
                    'is_closed' => (bool) ($cfg['is_closed'] ?? false),
                ]);
            }
        });

        // Also update the JSON field in Store model for redundancy/performance if needed
        $store->update(['operating_hours' => $hours]);

        try {
            app(\App\Services\StoreOperationalService::class)->refreshStatus();
        } catch (\Throwable $e) {
            Log::error('Failed refreshing store operational status', [
                'store_id' => $store->id,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
