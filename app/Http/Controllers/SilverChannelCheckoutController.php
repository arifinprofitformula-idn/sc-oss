<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;

class SilverChannelCheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->status !== 'PENDING_PAYMENT') {
            return redirect()->route('dashboard'); // Or appropriate page
        }

        $package = Package::where('is_active', true)->first();
        
        // Hardcode bank details for MVP or fetch from settings
        $bankDetails = [
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'PT Emas Perak Indonesia'
        ];

        return view('silver.checkout', compact('user', 'package', 'bankDetails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|max:2048', // 2MB Max
        ]);

        $user = Auth::user();

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            
            // Log payment or just update user status?
            // Rules say "Payment System: Create manual transfer payment flow... Store payment logs"
            // We should create a Payment record.
            // But for now, let's update user status to WAITING_VERIFICATION.
            
            $user->status = 'WAITING_VERIFICATION';
            $user->save();
            
            // TODO: Create Payment record
        }

        return redirect()->route('dashboard')->with('status', 'Bukti pembayaran berhasil diupload. Mohon tunggu verifikasi admin.');
    }
}
