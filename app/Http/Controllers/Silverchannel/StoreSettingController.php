<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\RajaOngkirService;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreSettingController extends Controller
{
    protected $rajaOngkirService;
    protected $integrationService;

    public function __construct(RajaOngkirService $rajaOngkirService, IntegrationService $integrationService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
        $this->integrationService = $integrationService;
    }

    public function edit()
    {
        // Check if enabled
        if (!$this->integrationService->get('silverchannel_store_menu_active')) {
            abort(403, 'Fitur pengaturan toko belum diaktifkan.');
        }

        $user = Auth::user();
        $store = Store::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name . "'s Store", 'slug' => Str::slug($user->name . "'s Store")]
        );

        $provinces = [];
        try {
            $provinces = $this->rajaOngkirService->getProvinces();
        } catch (\Exception $e) {
            // Handle error gracefully (maybe log it)
            // $provinces = [];
        }

        return view('silverchannel.store.settings', compact('store', 'provinces'));
    }

    public function update(Request $request)
    {
        if (!$this->integrationService->get('silverchannel_store_menu_active')) {
            abort(403, 'Fitur pengaturan toko belum diaktifkan.');
        }

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

            'social_links' => 'nullable|array',
            'operating_hours' => 'nullable|array',
        ]);

        $data = $request->except(['logo', '_token', '_method']);
        
        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($store->logo_path) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $path = $request->file('logo')->store('stores/logos', 'public');
            $data['logo_path'] = $path;
        }

        // Handle Slug update if name changed
        if ($request->name !== $store->name) {
            $data['slug'] = Str::slug($request->name);
        }

        $oldValues = $store->toArray();
        $store->update($data);
        $newValues = $store->getChanges();

        if (!empty($newValues)) {
            \App\Models\AuditLog::log('update_store_settings', $store, $oldValues, $newValues);
        }

        return back()->with('success', 'Pengaturan toko berhasil diperbarui.');
    }

    public function getCities($provinceId)
    {
        try {
            $cities = $this->rajaOngkirService->getCities($provinceId);
            return response()->json($cities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSubdistricts($cityId)
    {
        try {
            $subdistricts = $this->rajaOngkirService->getSubdistricts($cityId);
            return response()->json($subdistricts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
