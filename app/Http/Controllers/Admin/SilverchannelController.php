<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\Admin\StoreSilverchannelRequest;
use App\Http\Requests\Admin\UpdateSilverchannelRequest;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Hash;

use App\Models\AuditLog;

class SilverchannelController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    public function index(Request $request)
    {
        $query = User::role('SILVERCHANNEL')->with('referrer')->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }

        $silverchannels = $query->paginate(10);
        return view('admin.silverchannels.index', compact('silverchannels'));
    }

    public function store(StoreSilverchannelRequest $request)
    {
        $validated = $request->validated();

        // Generate ID Silverchannel
        $silverChannelId = $this->generateSilverChannelId($validated['name']);
        
        // Use ID as Referral Code
        $referralCode = $silverChannelId;

        $referrerId = null;
        if (!empty($validated['referrer_code'])) {
            $referrer = User::where('referral_code', $validated['referrer_code'])->first();
            $referrerId = $referrer ? $referrer->id : null;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['whatsapp'],
            'whatsapp' => $validated['whatsapp'],
            'province_id' => $validated['province_id'],
            'province_name' => $validated['province_name'],
            'city_id' => $validated['city_id'],
            'city_name' => $validated['city_name'],
            'referrer_id' => $referrerId,
            'referral_code' => $referralCode,
            'silver_channel_id' => $silverChannelId,
            'password' => Hash::make('password'), // Default password, maybe send email later
            'status' => 'ACTIVE', // Admin created, assume active
        ]);

        $user->assignRole('SILVERCHANNEL');

        return redirect()->route('admin.silverchannels.index')->with('success', 'Silverchannel created successfully.');
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

    public function update(UpdateSilverchannelRequest $request, User $user)
    {
        // Ensure we are updating a Silverchannel
        if (!$user->hasRole('SILVERCHANNEL')) {
             return back()->with('error', 'User is not a Silverchannel.');
        }

        $validated = $request->validated();

        $referrerId = $user->referrer_id;
        if (isset($validated['referrer_code'])) {
             if (empty($validated['referrer_code'])) {
                 $referrerId = null;
             } else {
                 $referrer = User::where('referral_code', $validated['referrer_code'])->first();
                 $referrerId = $referrer ? $referrer->id : null;
             }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['whatsapp'],
            'whatsapp' => $validated['whatsapp'],
            'province_id' => $validated['province_id'],
            'province_name' => $validated['province_name'],
            'city_id' => $validated['city_id'],
            'city_name' => $validated['city_name'],
            'referrer_id' => $referrerId,
        ];

        // Handle password update if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            AuditLog::log('UPDATE_PASSWORD', $user, null, ['password_changed' => true, 'by' => auth()->user()->name]);
        }

        $user->update($updateData);

        return redirect()->route('admin.silverchannels.index')->with('success', 'Silverchannel updated successfully.');
    }

    public function updatePassword(Request $request, User $user)
    {
        if (!$user->hasRole('SILVERCHANNEL')) {
             return back()->with('error', 'User is not a Silverchannel.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLog::log('UPDATE_PASSWORD', $user, null, ['password_changed' => true]);

        return redirect()->route('admin.silverchannels.index')->with('success', 'Password updated successfully.');
    }

    public function destroy(User $user)
    {
        if (!$user->hasRole('SILVERCHANNEL')) {
            return back()->with('error', 'User is not a Silverchannel.');
        }

        $user->delete();

        return redirect()->route('admin.silverchannels.index')->with('success', 'Silverchannel deleted successfully.');
    }

    public function approve(User $user)
    {
        if ($user->status === 'ACTIVE') {
             return back()->with('error', 'User already active.');
        }

        $user->status = 'ACTIVE';
        
        // Ensure Silverchannel ID and Referral Code are set and synced
        if (empty($user->silver_channel_id)) {
             $user->silver_channel_id = $this->generateSilverChannelId($user->name);
        }
        
        // Sync referral code
        $user->referral_code = $user->silver_channel_id;

        $user->save();

        // Trigger SilverchannelApproved event for Commission
        \App\Events\SilverchannelApproved::dispatch($user);

        return back()->with('success', 'Silverchannel approved successfully.');
    }

    public function reject(User $user)
    {
        $user->status = 'REJECTED';
        $user->save();

        return back()->with('success', 'Silverchannel rejected.');
    }

    // Helper methods for locations
    public function getProvinces()
    {
        return response()->json($this->rajaOngkir->getProvinces());
    }

    public function getCities($provinceId)
    {
        return response()->json($this->rajaOngkir->getCities($provinceId));
    }
}
