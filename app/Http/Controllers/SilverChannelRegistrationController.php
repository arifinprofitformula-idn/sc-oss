<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SilverChannelRegistrationController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function create()
    {
        // Check if registration is active (can be a setting, for now assume true or check a package)
        $packages = Package::active()->get();
        
        if ($packages->isEmpty()) {
             // Create default package if none exists for MVP
             $package = Package::create([
                 'name' => 'Silverchannel Basic',
                 'price' => 500000,
                 'benefits' => ['Akses Produk Silver', 'Komisi Referral', 'Support Prioritas'],
                 'is_active' => true
             ]);
             $packages = collect([$package]);
        }

        return view('auth.register-silver', compact('packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'nik' => ['required', 'numeric', 'digits:16', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'whatsapp' => ['required', 'string', 'regex:/^\+62[0-9]+$/'],
            'province_id' => ['required', 'string'],
            'province_name' => ['required', 'string'],
            'city_id' => ['required', 'string'],
            'city_name' => ['required', 'string'],
            // 'address' => ['required', 'string', 'min:10'], // Address not strictly requested in prompt "Form Pendaftaran Wajib" list, but usually part of city selection or separate? Prompt says "City (Kota) ... wajib diisi". Let's keep address if it's there or make it optional. User didn't list Address in "Form Pendaftaran Wajib", only City. But usually we need full address. I will make it optional or just stick to City for now to strictly follow prompt, but User model has address. Let's make it nullable or inferred.
            'referral_code' => ['nullable', 'string', 'max:10', 'exists:users,referral_code'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $referrer = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }

        // Generate ID Silverchannel
        $silverChannelId = $this->generateSilverChannelId($request->name);

        // Use ID as Referral Code
        $referralCode = $silverChannelId;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
            'whatsapp' => $request->whatsapp,
            'phone' => $request->whatsapp,
            'province_id' => $request->province_id,
            'province_name' => $request->province_name,
            'city_id' => $request->city_id,
            'city_name' => $request->city_name,
            'address' => $request->address ?? $request->city_name, // Fallback
            'referrer_id' => $referrer ? $referrer->id : null,
            'referral_code' => $referralCode,
            'silver_channel_id' => $silverChannelId,
            'password' => Hash::make($request->password),
            'status' => 'PENDING_PAYMENT', // User asked for flow: Register -> Profile. Payment might come later or "PENDING_REVIEW". Prompt says "Setelah registrasi berhasil, redirect ke /profile".
        ]);

        // Create empty profile
        $user->profile()->create();

        // Assign Role
        // Ensure role exists
        $role = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
        $user->assignRole($role);

        event(new Registered($user));

        Auth::login($user);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'REGISTER_SILVERCHANNEL',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => $user->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('profile.edit');
    }

    private function generateSilverChannelId($name)
    {
        // EPISC + 2 huruf pertama nama (uppercase) + 2 huruf random (A-Z) + 2 angka random (0-9)
        $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 2));
        // Pad if name is too short (though validation min 3)
        if (strlen($namePart) < 2) {
            $namePart = str_pad($namePart, 2, 'X');
        }

        $maxRetries = 3;
        $attempt = 0;

        do {
            $randomChars = strtoupper(Str::random(2));
            $randomNums = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $id = 'EPISC' . $namePart . $randomChars . $randomNums;
            
            // Check uniqueness
            if (!User::where('silver_channel_id', $id)->exists() && !User::where('referral_code', $id)->exists()) {
                return $id;
            }
            $attempt++;
        } while ($attempt < $maxRetries);

        // Fallback if collision persists (unlikely)
        return 'EPISC' . $namePart . strtoupper(Str::random(4));
    }

    // Public API for Location
    public function getProvinces()
    {
        try {
            $provinces = $this->rajaOngkir->getProvinces();
            return response()->json($provinces);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCities($provinceId)
    {
        try {
            $cities = $this->rajaOngkir->getCities($provinceId);
            return response()->json($cities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
