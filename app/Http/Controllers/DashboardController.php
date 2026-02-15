<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CommissionLedger;
use App\Models\Order;
use App\Models\Product;
use App\Models\EpiProductMapping;
use App\Exports\ProductPricelistExport;
use Maatwebsite\Excel\Facades\Excel;
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
            
            // Pricelist Update Info
            $lastUpdate = Product::where('is_active', true)->max('updated_at');
            $data = compact('referralCount', 'totalCommission', 'referralLink', 'storeStatus', 'lastUpdate');
            
            // Add Recent Orders for Silverchannel
            $recentOrders = Order::where('user_id', $user->id)->latest()->take(5)->get();
            $data['recentOrders'] = $recentOrders;
        }

        return view('dashboard', $data);
    }

    public function getPricelist(Request $request)
    {
        $query = Product::where('is_active', true)->with('latestPriceHistory');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort_field', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        // Whitelist sort fields
        $allowedSorts = ['name', 'price_silverchannel', 'price_customer', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(10)->appends($request->query());

        return view('dashboard.partials.pricelist-table', compact('products'));
    }

    public function exportPricelist(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $fileName = 'pricelist-' . now()->format('Y-m-d');

        if ($format === 'pdf') {
            return Excel::download(new ProductPricelistExport, $fileName . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return Excel::download(new ProductPricelistExport, $fileName . '.xlsx');
    }

    public function exportPricelistPdf()
    {
        return Excel::download(new ProductPricelistExport, 'pricelist-' . now()->format('Y-m-d') . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }
    
    public function lastPriceUpdate()
    {
        $ts = class_exists(\App\Models\EpiProductMapping::class)
            ? EpiProductMapping::max('last_synced_at')
            : Product::where('is_active', true)->max('updated_at');
        return response()->json([
            'last_update' => $ts ? \Carbon\Carbon::parse($ts)->toIso8601String() : null
        ]);
    }
}
