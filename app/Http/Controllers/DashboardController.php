<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CommissionLedger;
use App\Models\Order;
use App\Services\StoreOperationalService;

class DashboardController extends Controller
{
    protected $storeOperationalService;

    public function __construct(StoreOperationalService $storeOperationalService)
    {
        $this->storeOperationalService = $storeOperationalService;
    }

    /**
     * Display the dashboard.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Redirect Admin to Admin Dashboard
        if ($user->hasRole('SUPER_ADMIN')) {
            return redirect()->route('admin.dashboard');
        }

        $data = [];

        if ($user->hasRole('SILVERCHANNEL')) {
            // Store Operational Status
            $storeStatus = $this->storeOperationalService->getStatus();
            
            // Referral Stats
            $referralCount = User::where('referrer_id', $user->id)->count();
            
            // Commission Stats (Total Earned)
            $totalCommission = CommissionLedger::where('user_id', $user->id)
                ->where('type', 'REGISTRATION') // Assuming specifically referral commissions
                ->sum('amount'); // Check if 'amount' is correct column, implied from context
                
            $referralLink = route('register', ['ref' => $user->referral_code]);
            
            $data = compact('referralCount', 'totalCommission', 'referralLink', 'storeStatus');
            
            // Add Recent Orders for Silverchannel
            $recentOrders = Order::where('user_id', $user->id)->latest()->take(5)->get();
            $data['recentOrders'] = $recentOrders;
        }

        return view('dashboard', $data);
    }
}
