<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\Payout\PayoutService;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function index(Request $request)
    {
        $query = Payout::with('user')->latest();

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Export logic can be added here similar to PaymentController

        $payouts = $query->paginate(15);

        return view('admin.payouts.index', compact('payouts'));
    }

    public function show(Payout $payout)
    {
        $payout->load('user', 'commissionLedger');
        return view('admin.payouts.show', compact('payout'));
    }

    public function approve(Request $request, Payout $payout)
    {
        $request->validate([
            'proof_file' => 'required|file|image|max:2048',
        ]);

        try {
            // Robust File Storage to avoid "Path must not be empty" error
            $file = $request->file('proof_file');
            
            // Ensure directory exists
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('payout-proofs')) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('payout-proofs');
            }
            
            $filename = 'payout-proofs/' . $file->hashName();
            
            // Use file_get_contents + Storage::put instead of store() / putFile()
            $content = file_get_contents($file->getRealPath() ?: $file->getPathname());
            
            if ($content === false) {
                throw new \Exception("Gagal membaca file bukti pembayaran.");
            }
            
            if (!\Illuminate\Support\Facades\Storage::disk('public')->put($filename, $content)) {
                throw new \Exception("Gagal menyimpan file ke storage.");
            }

            $path = $filename;
            $this->payoutService->approve($payout, $path);

            return back()->with('success', 'Payout approved and processed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Payout $payout)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $this->payoutService->reject($payout, $request->reason);
            return back()->with('success', 'Payout rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
