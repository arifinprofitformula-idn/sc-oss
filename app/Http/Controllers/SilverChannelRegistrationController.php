<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Store;
use App\Models\StoreSetting;
use App\Services\ApiIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationPendingApproval;
use App\Mail\NewRegistrationAlert;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\AuditLog;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SilverChannelRegistrationController extends Controller
{
    protected $shippingService;

    public function __construct(ApiIdService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    public function create(Request $request)
    {
        // 1. Get Active Package with Caching (30 mins)
        // Eager load products.category for insurance calculation
        $package = Cache::remember('active_silver_package', 1800, function () {
            return Package::active()->with('products.category')->first();
        });

        // Log the access
        Log::info('SilverChannel Registration Page Accessed', [
            'ip' => $request->ip(),
            'package_available' => $package ? true : false,
            'package_id' => $package ? $package->id : null
        ]);
        
        // 2. Handle Referral Code
        // Priority: URL Param > Cookie > Null
        $referralCode = $request->query('ref', Cookie::get('referral_code'));

        // 3. Get Packing Fee
        $integrationService = app(\App\Services\IntegrationService::class);
        $packingFee = (int) $integrationService->get('shipping_packing_fee', 0);

        return view('auth.register-silver', compact('package', 'referralCode', 'packingFee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'nik' => ['required', 'numeric', 'digits:16', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'whatsapp' => [
                'required', 
                'string', 
                'regex:/^62[0-9]+$/', 
                'min:10', 
                'max:15',
                function ($attribute, $value, $fail) {
                    $formatted = '+' . $value;
                    if (User::where('phone', $formatted)->orWhere('whatsapp', $formatted)->exists()) {
                        $fail('Nomor WhatsApp sudah terdaftar. Silakan gunakan nomor lain.');
                    }
                },
            ],
            'province_id' => ['required', 'string'],
            'province_name' => ['required', 'string'],
            'city_id' => ['required', 'string'],
            'city_name' => ['required', 'string'],
            'subdistrict_id' => ['nullable', 'string'],
            'subdistrict_name' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            // Address is optional in prompt but good to have if user provides it
            'address' => ['nullable', 'string'],
            'referral_code' => ['required', 'string', 'max:20', 'exists:users,referral_code'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'package_id' => ['required', 'exists:packages,id'],
            'shipping_service' => ['required', 'string'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'shipping_courier' => ['required', 'string'],
            'shipping_etd' => ['nullable', 'string'],
            'terms_accepted' => ['required', 'accepted'],
        ], [
            'referral_code.required' => 'Kode Referral harus diisi.',
            'referral_code.exists' => 'Kode referral tidak valid.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
            'shipping_service.required' => 'Silakan pilih layanan pengiriman.',
        ]);

        // Generate Token
        $token = Str::random(40);
        $cacheKey = 'silver_reg_' . $token;
        
        $data = $request->except(['password_confirmation']);
        // Transform whatsapp to standard format +62
        $data['whatsapp'] = '+' . $data['whatsapp'];
        
        // Add Packing Fee
        $integrationService = app(\App\Services\IntegrationService::class);
        $packingFee = (int) $integrationService->get('shipping_packing_fee', 0);
        $data['packing_fee'] = $packingFee;

        // Store in cache for 2 hours (enough for checkout)
        // Using Cache instead of Session for better isolation and no-cookie dependency for data persistence across potential browser issues
        \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 7200);

        return redirect()->route('register.silver.checkout', ['token' => $token]);
    }

    public function checkout($token)
    {
        $cacheKey = 'silver_reg_' . $token;
        $data = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$data) {
            return redirect()->route('register.silver')->withErrors(['error' => 'Sesi pendaftaran kadaluarsa, silakan ulangi.']);
        }

        $package = Package::with('products.category')->find($data['package_id']);
        if (!$package) {
             return redirect()->route('register.silver')->withErrors(['package_id' => 'Paket tidak ditemukan.']);
        }
        
        // Get Main Store Bank Details (Super Admin's Store)
        $mainStore = Store::whereHas('user.roles', function($q) {
            $q->where('name', 'SUPER_ADMIN');
        })->first();

        $banks = [];

        // Fallback to StoreSetting if Store model is empty (legacy support)
        if ($mainStore && !empty($mainStore->bank_details)) {
            $banks = $mainStore->bank_details;
        } else {
            $storeSetting = StoreSetting::first();
            if ($storeSetting && !empty($storeSetting->bank_info)) {
                 $banks = $storeSetting->bank_info;
            }
        }
        
        return view('auth.register-checkout', compact('data', 'package', 'banks', 'token'));
    }

    public function payment(Request $request, $token)
    {
        $request->validate([
            'payment_proof' => ['required', 'image', 'max:2048'], // 2MB
        ]);

        $cacheKey = 'silver_reg_' . $token;
        $data = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$data) {
            return redirect()->route('register.silver')->withErrors(['error' => 'Sesi pendaftaran kadaluarsa, silakan ulangi.']);
        }

        $package = Package::with('products.category')->find($data['package_id']);

        DB::beginTransaction();
        try {
            // 1. Create User
            $referrer = null;
            if (!empty($data['referral_code'])) {
                $referrer = User::where('referral_code', $data['referral_code'])->first();
            }

            $silverChannelId = $this->generateSilverChannelId($data['name']);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'nik' => $data['nik'],
                'whatsapp' => $data['whatsapp'],
                'phone' => $data['whatsapp'],
                'province_id' => $data['province_id'],
                'province_name' => $data['province_name'],
                'city_id' => $data['city_id'],
                'city_name' => $data['city_name'],
                'subdistrict_id' => $data['subdistrict_id'] ?? null,
                'subdistrict_name' => $data['subdistrict_name'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'address' => $data['address'] ?? $data['city_name'],
                'referrer_id' => $referrer ? $referrer->id : null,
                'referral_code' => $silverChannelId, // Self referral code
                'silver_channel_id' => $silverChannelId,
                'password' => Hash::make($data['password']),
                'status' => 'WAITING_VERIFICATION', // Active after payment verification
            ]);

            $user->profile()->create();
            
            $role = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
            $user->assignRole($role);

            // 2. Create Order for Package
            $shippingCost = $data['shipping_cost'] ?? 0;
            $packingFee = $data['packing_fee'] ?? 0;
            $shippingCost += $packingFee; // Merge packing fee into shipping cost
            
            // Calculate totals
            $baseTotal = $package->base_total;
            $insuranceCost = $package->insurance_cost;
            $grandTotal = $baseTotal + $insuranceCost + $shippingCost;

            $shippingAddress = $data['address'];
            if (!empty($data['village_name'])) {
                $shippingAddress .= ', ' . $data['village_name'];
            }
            if (!empty($data['subdistrict_name'])) {
                $shippingAddress .= ', ' . $data['subdistrict_name'];
            }
            $shippingAddress .= ', ' . $data['city_name'] . ', ' . $data['province_name'];
            if (!empty($data['postal_code'])) {
                $shippingAddress .= ' ' . $data['postal_code'];
            }

            $order = Order::create([
                'user_id' => $user->id,
                // 'store_id' => 1, // Default store / Head office (Disabled: Column missing in MVP)
                'order_number' => 'REG-' . strtoupper(Str::random(10)),
                'total_amount' => $grandTotal, // Final amount to pay
                'subtotal' => $baseTotal, // Base price + Products
                'insurance_amount' => $insuranceCost,
                'status' => 'WAITING_VERIFICATION', // Waiting for admin to verify payment
                'payment_status' => 'PAID', // Marked as paid by user (uploaded proof), pending verification
                'shipping_cost' => $shippingCost,
                'shipping_courier' => $data['shipping_courier'] ?? 'jne',
                'shipping_service' => $data['shipping_service'] ?? null,
                'shipping_address' => $shippingAddress,
            ]);

            // Add Order Item (Package)
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => null, // It's a package
                'product_name' => $package->name . ' (Paket Pendaftaran)',
                'quantity' => 1,
                'price' => $package->price,
                'total' => $package->price,
            ]);

            // Add Order Items (Bundled Products)
            foreach ($package->products as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name . ' (Bundling Paket)',
                    'quantity' => $product->pivot->quantity,
                    'price' => $product->price_silverchannel,
                    'total' => $product->price_silverchannel * $product->pivot->quantity,
                ]);
            }

            // 3. Store Payment Proof
            try {
                $proofPath = $this->processPaymentProof($request->file('payment_proof'));
            } catch (\Exception $e) {
                // Fallback to simple store if processing fails, though unlikely
                 $file = $request->file('payment_proof');
                 if (!Storage::disk('public')->exists('payment-proofs')) {
                    Storage::disk('public')->makeDirectory('payment-proofs');
                 }
                 $filename = 'payment-proofs/' . $file->hashName();
                 Storage::disk('public')->put($filename, file_get_contents($file->getRealPath() ?: $file->getPathname()));
                 $proofPath = $filename;
            }

            Payment::create([
                'order_id' => $order->id,
                'amount' => $grandTotal,
                'method' => 'MANUAL_TRANSFER',
                'proof_file' => $proofPath,
                'status' => 'PENDING_VERIFICATION', // Admin needs to verify
            ]);

            event(new Registered($user));

            // Log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'REGISTER_SILVERCHANNEL_WITH_PAYMENT',
                'model_type' => User::class,
                'model_id' => $user->id,
                'new_values' => ['package_id' => $package->id, 'order_id' => $order->id],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Clear session/cache
            \Illuminate\Support\Facades\Cache::forget($cacheKey);

            // Send Emails
            try {
                // To User
                Mail::to($user->email)->send(new RegistrationPendingApproval($user, $order));

                // To Admins (Super Admin)
                $admins = User::role('SUPER_ADMIN')->get();
                if ($admins->isEmpty()) {
                    // Fallback to a system notification email if no admin user found
                     Mail::to(config('mail.from.address'))->send(new NewRegistrationAlert($user, $order));
                } else {
                    foreach ($admins as $admin) {
                        Mail::to($admin->email)->send(new NewRegistrationAlert($user, $order));
                    }
                }
            } catch (\Exception $e) {
                // Log email error but don't fail the transaction
                \Illuminate\Support\Facades\Log::error('Registration Email Failed: ' . $e->getMessage());
            }

            // Log the user in
            Auth::login($user);

            // Redirect to a specific "Waiting Approval" page instead of login
            return redirect()->route('approval.notice')->with('success', 'Pendaftaran berhasil! Mohon tunggu verifikasi admin.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    private function generateSilverChannelId($name)
    {
        $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 2));
        if (strlen($namePart) < 2) {
            $namePart = str_pad($namePart, 2, 'X');
        }

        $maxRetries = 3;
        $attempt = 0;

        do {
            $randomChars = strtoupper(Str::random(2));
            $randomNums = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $id = 'EPISC' . $namePart . $randomChars . $randomNums;
            
            if (!User::where('silver_channel_id', $id)->exists() && !User::where('referral_code', $id)->exists()) {
                return $id;
            }
            $attempt++;
        } while ($attempt < $maxRetries);

        return 'EPISC' . $namePart . strtoupper(Str::random(4));
    }

    // Public API for Location
    public function getProvinces()
    {
        try {
            $provinces = $this->shippingService->getProvinces();
            return response()->json($provinces);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCities($provinceId)
    {
        try {
            $cities = $this->shippingService->getCities($provinceId);
            return response()->json($cities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSubdistricts($cityId)
    {
        try {
            $subdistricts = $this->shippingService->getSubdistricts($cityId);
            return response()->json($subdistricts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getVillages($subdistrictId)
    {
        try {
            $villages = $this->shippingService->getVillages($subdistrictId);
            return response()->json($villages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getShippingServices(Request $request)
    {
        $request->validate([
            'destination' => 'required', // village_id
            'destination_type' => 'nullable|in:city,subdistrict,village',
            'weight' => 'required|integer|min:1',
            'courier' => 'nullable|string'
        ]);

        // Get Origin from StoreSetting (Assuming Central Store/Admin)
        $storeSetting = StoreSetting::first();
        
        // Default to Pademangan, Jakarta Utara (3172051003) for API ID context if not configured
        // Validated working code for API ID
        $origin = '3172051003'; 

        // If using API ID, we need village code.
        // If we were using RajaOngkir, we would use city/subdistrict.
        // Ideally we check which service is active, but here we are injected with ApiIdService
        
        $courier = $request->courier ?? 'jne'; // Default to JNE if not specified
        
        try {
            // ApiIdService::getCost($origin, $destination, $weight, $courier)
            // Origin and Destination must be Village Codes
            $costs = $this->shippingService->getCost(
                $origin,
                $request->destination,
                $request->weight,
                $courier
            );
            return response()->json($costs);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Process payment proof image with compression and resizing.
     * Mirrors logic from PackageController/UserProfileController.
     */
    private function processPaymentProof($file)
    {
        $imageContents = '';
        $extension = $file->getClientOriginalExtension();
        $targetExtension = 'jpg'; // Standardize to JPG for consistency

        // If > 1MB, compress
        if ($file->getSize() > 1024 * 1024) {
            $imageResource = imagecreatefromstring($file->get());
            if (!$imageResource) {
                 // Fallback if GD fails or format not supported (Robust Storage)
                 if (!Storage::disk('public')->exists('payment-proofs')) {
                     Storage::disk('public')->makeDirectory('payment-proofs');
                 }
                 $filename = 'payment-proofs/' . $file->hashName();
                 Storage::disk('public')->put($filename, file_get_contents($file->getRealPath() ?: $file->getPathname()));
                 return $filename;
            }

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
                
                // Handle transparency
                $white = imagecolorallocate($newImage, 255, 255, 255);
                imagefill($newImage, 0, 0, $white);
                
                imagecopyresampled($newImage, $imageResource, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $width, $height);
                $imageResource = $newImage;
            } else {
                $newImage = imagecreatetruecolor($width, $height);
                $white = imagecolorallocate($newImage, 255, 255, 255);
                imagefill($newImage, 0, 0, $white);
                imagecopy($newImage, $imageResource, 0, 0, 0, 0, $width, $height);
                $imageResource = $newImage;
            }

            // Output to buffer
            ob_start();
            imagejpeg($imageResource, null, 80); // 80% quality
            $imageContents = ob_get_clean();
        } else {
            // If < 1MB, convert to JPG for consistency
             $imageResource = @imagecreatefromstring($file->get());
             if ($imageResource) {
                $width = imagesx($imageResource);
                $height = imagesy($imageResource);
                
                $newImage = imagecreatetruecolor($width, $height);
                $white = imagecolorallocate($newImage, 255, 255, 255);
                imagefill($newImage, 0, 0, $white);
                imagecopy($newImage, $imageResource, 0, 0, 0, 0, $width, $height);
                
                ob_start();
                imagejpeg($newImage, null, 90); 
                $imageContents = ob_get_clean();
             } else {
                 $imageContents = $file->get();
                 $targetExtension = $extension;
             }
        }

        $filename = 'payment-proofs/' . Str::uuid() . '.' . $targetExtension;
        
        if (!Storage::disk('public')->put($filename, $imageContents)) {
             throw new \Exception('Gagal menyimpan file bukti pembayaran.');
        }
        
        return $filename;
    }
}
