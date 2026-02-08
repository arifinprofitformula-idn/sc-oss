<?php

namespace App\Http\Controllers;

use App\Services\Payout\PayoutService;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    protected $payoutService;
    protected $integrationService;

    public function __construct(PayoutService $payoutService, IntegrationService $integrationService)
    {
        $this->payoutService = $payoutService;
        $this->integrationService = $integrationService;
    }

    public function index()
    {
        $user = Auth::user();
        $payouts = $user->payouts()->latest()->paginate(10, ['*'], 'payouts_page');
        
        $commissions = $user->commissionLedgers()
            ->with('reference')
            ->latest()
            ->paginate(10, ['*'], 'commissions_page');

        $balance = $user->wallet_balance;
        $pendingCommission = $user->pending_commission;
        $holdingPeriod = $this->integrationService->get('commission_holding_period', 7);

        return view('payouts.index', compact('payouts', 'commissions', 'balance', 'pendingCommission', 'holdingPeriod'));
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
