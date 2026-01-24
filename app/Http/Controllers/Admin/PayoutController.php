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
            $path = $request->file('proof_file')->store('payout-proofs', 'public');
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
