<?php

namespace App\Http\Controllers;

use App\Services\Payout\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function index()
    {
        $user = Auth::user();
        $payouts = $user->payouts()->latest()->paginate(10);
        $balance = $user->wallet_balance;
        $pendingCommission = $user->pending_commission;

        return view('payouts.index', compact('payouts', 'balance', 'pendingCommission'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        try {
            $this->payoutService->createRequest($request->user(), $request->amount);

            return back()->with('success', 'Payout requested successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
