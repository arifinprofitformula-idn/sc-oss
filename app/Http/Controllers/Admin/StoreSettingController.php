<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreSettingController extends Controller
{
    public function edit()
    {
        $setting = StoreSetting::firstOrFail();
        return view('admin.settings.store', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = StoreSetting::firstOrFail();

        $request->validate([
            'distributor_name' => 'required|string|max:255',
            'distributor_address' => 'required|string',
            'distributor_phone' => 'required|string|max:20',
            'logo' => 'nullable|image|max:2048', // 2MB Max
            'unique_code_enabled' => 'boolean',
            'unique_code_range_start' => 'required_if:unique_code_enabled,1|integer|min:1',
            'unique_code_range_end' => 'required_if:unique_code_enabled,1|integer|gt:unique_code_range_start',
            'bank_info' => 'required|array',
            'bank_info.*.bank' => 'required|string',
            'bank_info.*.account_number' => 'required|string',
            'bank_info.*.account_name' => 'required|string',
        ]);

        $data = $request->except(['logo', 'bank_info']);
        
        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('store-settings', 'public');
        }

        // Handle Bank Info (ensure it's array)
        $data['bank_info'] = $request->bank_info;
        
        // Handle boolean checkbox (if unchecked it's not present)
        $data['unique_code_enabled'] = $request->has('unique_code_enabled');

        $setting->update($data);

        return redirect()->route('admin.settings.store')->with('success', 'Pengaturan toko berhasil diperbarui.');
    }
}
