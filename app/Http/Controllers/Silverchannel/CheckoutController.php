<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\RajaOngkirService;
use App\Services\ShippingService;
use App\Services\StoreOperationalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\IntegrationService;

class CheckoutController extends Controller
{
    protected $orderService;
    protected $rajaOngkirService;
    protected $shippingService;
    protected $storeOperationalService;
    protected $integrationService;

    public function __construct(
        OrderService $orderService,
        RajaOngkirService $rajaOngkirService,
        ShippingService $shippingService,
        StoreOperationalService $storeOperationalService,
        IntegrationService $integrationService
    ) {
        $this->orderService = $orderService;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->shippingService = $shippingService;
        $this->storeOperationalService = $storeOperationalService;
        $this->integrationService = $integrationService;
    }

    public function orderReceived(Request $request, \App\Models\Order $order)
    {
        // Verify key
        if ($request->query('key') !== $order->order_key) {
            abort(403, 'Invalid order key.');
        }

        // Verify user ownership
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($order->user_id !== Auth::id() && (!$user || !$user->hasRole('SUPER_ADMIN'))) {
             abort(403, 'Unauthorized.');
        }

        // Get Main Store Settings for Payment Info
        $mainStoreId = $this->integrationService->get('silverchannel_primary_store_id');
        $store = null;
        if ($mainStoreId) {
            $store = \App\Models\Store::find($mainStoreId);
        }
        
        // Fallback: Get the first store (usually Super Admin's store)
        if (!$store) {
            $store = \App\Models\Store::orderBy('id', 'asc')->first();
        }

        $paymentTimeout = (int) $this->integrationService->get('store_payment_timeout', 60);
        $expiryTime = $order->created_at->addMinutes($paymentTimeout);

        // Auto-cancel if expired and still waiting for payment
        if (now()->greaterThan($expiryTime) && $order->status === \App\Services\OrderService::STATUS_WAITING_PAYMENT) {
            $this->orderService->updateStatus(
                $order, 
                \App\Services\OrderService::STATUS_CANCELLED, 
                'System Auto-Cancel (Payment Timeout)', 
                null // System action
            );
        }

        return view('silverchannel.checkout.order-received', compact('order', 'store', 'expiryTime'));
    }

    public function index()
    {
        $status = $this->storeOperationalService->getStatus();
        if (empty($status['can_add_to_cart'])) {
            return redirect()->route('silverchannel.products.index')->with('error', 'Toko sedang tutup. Tidak dapat melakukan checkout.');
        }

        $user = User::findOrFail(Auth::id());
        $cartItems = $user->cart()->with('product')->get();
        $storeSetting = \App\Models\StoreSetting::first();
        $integrationService = app(\App\Services\IntegrationService::class);

        // Check address provider compatibility
        $currentProvider = $integrationService->get('shipping_provider', 'rajaongkir');
        if ($user->address_provider !== $currentProvider) {
            return redirect()->route('profile.edit')->with('warning', 'Sistem pengiriman telah diperbarui. Mohon perbarui alamat Anda untuk melanjutkan checkout.');
        }

        // 1. Get Global Active Couriers
        $globalCouriers = json_decode($integrationService->get('shipping_active_couriers', '[]'), true) ?? [];
        if (empty($globalCouriers)) {
            // Fallback to legacy setting
            $legacyCouriers = (string) $integrationService->get('rajaongkir_couriers', '');
            if (!empty($legacyCouriers)) {
                $globalCouriers = collect(explode(',', $legacyCouriers))
                    ->map(fn($c) => strtolower(trim($c)))
                    ->filter()
                    ->values()
                    ->all();
            } else {
                // Default fallback
                $globalCouriers = ['jne', 'pos', 'tiki', 'sicepat', 'jnt'];
            }
        }

        // 2. Get Store Specific Configuration
        $userStore = \App\Models\Store::where('user_id', $user->id)->first();
        $checkoutCouriers = $globalCouriers;
        
        $insuranceSettings = [
            'active' => (bool) $integrationService->get('shipping_insurance_active', 0),
            'percentage' => (float) $integrationService->get('shipping_insurance_percentage', 0),
            'description' => $integrationService->get('shipping_insurance_description', 'Layanan Asuransi Pengiriman'),
            'packing_fee' => (int) $integrationService->get('shipping_packing_fee', 0),
        ];

        if ($userStore && !empty($userStore->shipping_couriers)) {
            // Intersect to ensure only globally active couriers are allowed
            $checkoutCouriers = array_values(array_intersect($userStore->shipping_couriers, $globalCouriers));
        } elseif ($storeSetting && is_array($storeSetting->allowed_couriers) && !empty($storeSetting->allowed_couriers)) {
             // Fallback to old global store setting if user store config not set
            $checkoutCouriers = array_values(array_intersect(
                array_map('strtolower', $storeSetting->allowed_couriers),
                $globalCouriers
            ));
        }
        
        // Calculate totals for view (consistent with Cart logic)
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $item->price_final = $item->product->price_silverchannel ?? $item->product->price_final;
            $item->total = $item->price_final * $item->quantity;
            $subtotal += $item->total;
            $item->product->image_url = $item->product->image 
                ? asset('storage/' . $item->product->image) 
                : 'https://placehold.co/100';
        }
        
        // Generate Unique Code (for display purposes) if enabled
        $uniqueCodeActive = (bool) $integrationService->get('store_payment_unique_code_active', 0);
        $uniqueCode = 0;

        if ($uniqueCodeActive) {
            $uniqueCode = session('checkout_unique_code');
            if (!$uniqueCode) {
                $uniqueCode = rand(1, 999);
                session(['checkout_unique_code' => $uniqueCode]);
            }
        } else {
            session()->forget('checkout_unique_code');
        }

        return view('silverchannel.checkout.index', compact('user', 'cartItems', 'uniqueCode', 'uniqueCodeActive', 'storeSetting', 'checkoutCouriers', 'subtotal', 'insuranceSettings'));
    }

    public function calculateShipping(Request $request)
    {
        $request->validate([
            'destination_subdistrict_id' => 'required', // Can be subdistrict_id or village_id depending on provider
            'courier' => 'required|string',
        ]);

        $integrationService = app(\App\Services\IntegrationService::class);
        $shippingProvider = $integrationService->get('shipping_provider', 'rajaongkir');

        if (!(bool) $integrationService->get('rajaongkir_active', 0) && $shippingProvider === 'rajaongkir') {
            return response()->json(['success' => false, 'message' => 'Fitur perhitungan ongkir belum diaktifkan.'], 400);
        }

        // 1. Get Global Active Couriers
        $globalCouriers = json_decode($integrationService->get('shipping_active_couriers', '[]'), true) ?? [];
        if (empty($globalCouriers)) {
            $legacyCouriers = (string) $integrationService->get('rajaongkir_couriers', '');
            if (!empty($legacyCouriers)) {
                $globalCouriers = collect(explode(',', $legacyCouriers))
                    ->map(fn($c) => strtolower(trim($c)))
                    ->filter()
                    ->values()
                    ->all();
            } else {
                $globalCouriers = ['jne', 'pos', 'tiki', 'sicepat', 'jnt'];
            }
        }

        // 2. Get User's Store Configuration
        $user = User::findOrFail(Auth::id());
        $userStore = \App\Models\Store::where('user_id', $user->id)->first();
        
        $allowedCouriers = $globalCouriers;
        if ($userStore && !empty($userStore->shipping_couriers)) {
            $allowedCouriers = array_values(array_intersect($userStore->shipping_couriers, $globalCouriers));
        }

        $requestedCourier = strtolower($request->courier);

        if (!in_array($requestedCourier, $allowedCouriers, true)) {
            return response()->json(['success' => false, 'message' => 'Kurir yang dipilih tidak tersedia atau tidak aktif untuk toko ini.'], 422);
        }

        // Calculate weight from DB Cart to ensure synchronization
        // $user is already retrieved above
        $cartItems = $user->cart()->with('product')->get();

        if ($cartItems->isEmpty()) {
             return response()->json(['success' => false, 'message' => 'Cart is empty.'], 400);
        }

        $weight = $cartItems->sum(function ($item) {
            $rawWeight = (int) ($item->product->weight ?? 0);
            if ($rawWeight <= 0) {
                $rawWeight = (int) config('services.rajaongkir.default_weight', 1000);
            }

            return $rawWeight * $item->quantity;
        });

        // Determine Origin and Destination based on Provider
        $origin = null;
        $destination = $request->destination_subdistrict_id; // Frontend sends generic ID here
        $provider = null;

        if ($shippingProvider === 'api_id') {
            $origin = $integrationService->get('api_id_origin_id');
            $provider = $this->shippingService->getProvider('api_id');
        } else {
            $origin = $integrationService->get('rajaongkir_origin_id');
            $provider = $this->shippingService->getProvider('rajaongkir');
        }

        if (!$origin) {
            return response()->json(['success' => false, 'message' => 'Store origin not configured.'], 400);
        }

        try {
            $costs = $provider->getCost(
                $origin,
                $destination,
                $weight,
                $request->courier
            );

            return response()->json([
                'success' => true, 
                'data' => $costs,
                'total_weight' => $weight
            ]);
        } catch (\Exception $e) {
            Log::error('Shipping calculation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to calculate shipping.'], 500);
        }
    }

    public function store(Request $request)
    {
        $status = $this->storeOperationalService->getStatus();
        if (empty($status['can_add_to_cart'])) {
             if ($request->wantsJson()) {
                 return response()->json(['success' => false, 'message' => 'Toko sedang tutup. Tidak dapat memproses pesanan.'], 403);
             }
             return redirect()->route('silverchannel.products.index')->with('error', 'Toko sedang tutup. Tidak dapat memproses pesanan.');
        }

        $request->validate([
            // Items are loaded from DB, so we don't validate items array input
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.subdistrict_id' => 'required', // Needed for verification
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.village_name' => 'nullable|string',
            
            'shipping_service' => 'required|array',
            'shipping_service.courier' => 'required|string',
            'shipping_service.service' => 'required|string',
            // 'shipping_service.cost' => 'required|numeric', // We will verify this
            
            'payment_method' => 'required|string|in:transfer,cod',
        ]);

        try {
            // Load Cart Items from DB
            $user = User::findOrFail(Auth::id());
            
            // Enrich Shipping Address with Names if missing
            $shippingAddress = $request->shipping_address;
            
            // Helper to find name in list
            $findName = function($list, $idKey, $nameKey, $targetId) {
                foreach ($list as $item) {
                    if (($item[$idKey] ?? '') == $targetId) {
                        return $item[$nameKey] ?? null;
                    }
                }
                return null;
            };

            // 1. Province
            if (empty($shippingAddress['province_name']) && !empty($shippingAddress['province_id'])) {
                $provinces = $this->rajaOngkirService->getProvinces();
                $shippingAddress['province_name'] = $findName($provinces, 'province_id', 'province', $shippingAddress['province_id']) ?? 'Unknown Province';
            }

            // 2. City
            if (empty($shippingAddress['city_name']) && !empty($shippingAddress['city_id'])) {
                $cities = $this->rajaOngkirService->getCities($shippingAddress['province_id']);
                // Handle different structure between V1 and V2 if needed, but service normalizes it somewhat
                // RajaOngkirService::getCities returns array of cities.
                // V1: city_id, city_name, type
                $shippingAddress['city_name'] = $findName($cities, 'city_id', 'city_name', $shippingAddress['city_id']) ?? 'Unknown City';
                
                // Also get postal code if missing
                if (empty($shippingAddress['postal_code'])) {
                     foreach ($cities as $city) {
                        if (($city['city_id'] ?? '') == $shippingAddress['city_id']) {
                            $shippingAddress['postal_code'] = $city['postal_code'] ?? '';
                            break;
                        }
                    }
                }
            }

            // 3. Subdistrict
            if (empty($shippingAddress['subdistrict_name']) && !empty($shippingAddress['subdistrict_id'])) {
                // Ensure we have city_id
                if (!empty($shippingAddress['city_id'])) {
                    $subdistricts = $this->rajaOngkirService->getSubdistricts($shippingAddress['city_id']);
                    $shippingAddress['subdistrict_name'] = $findName($subdistricts, 'subdistrict_id', 'subdistrict_name', $shippingAddress['subdistrict_id']) ?? 'Unknown Subdistrict';
                }
            }
            
            // Update request with enriched address
            $request->merge(['shipping_address' => $shippingAddress]);

            $cartItems = $user->cart()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Cart is empty.'], 400);
            }

            // Calculate totals server-side
            $itemsData = [];
            $subtotal = 0;
            $totalWeight = 0;

            foreach ($cartItems as $item) {
                $product = $item->product;
                
                if ($product->stock < $item->quantity) {
                    throw new \Exception("Stok tidak mencukupi untuk produk: {$product->name}");
                }

                // Use silverchannel price
                $price = $product->price_silverchannel ?? $product->price_final; // Fallback
                $lineTotal = $price * $item->quantity;
                
                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'total' => $lineTotal,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ];

                $subtotal += $lineTotal;

                $rawWeight = (int) ($product->weight ?? 0);
                if ($rawWeight <= 0) {
                    $rawWeight = (int) config('services.rajaongkir.default_weight', 1000);
                }

                $totalWeight += $rawWeight * $item->quantity;
            }

            // Verify Shipping Cost
            $integrationService = app(\App\Services\IntegrationService::class);
            $shippingProvider = $integrationService->get('shipping_provider', 'rajaongkir');

            if (!(bool) $integrationService->get('rajaongkir_active', 0) && $shippingProvider === 'rajaongkir') {
                throw new \Exception('Konfigurasi perhitungan ongkir belum diaktifkan.');
            }

            $activeCouriersSetting = (string) $integrationService->get('rajaongkir_couriers', '');
            $activeCouriers = collect(explode(',', $activeCouriersSetting))
                ->map(function ($courier) {
                    return strtolower(trim($courier));
                })
                ->filter()
                ->values()
                ->all();

            // Override with global active couriers for API ID / generic
            if ($shippingProvider === 'api_id') {
                 $activeCouriers = json_decode($integrationService->get('shipping_active_couriers', '[]'), true) ?? [];
            }

            $requestedCourier = strtolower($request->shipping_service['courier']);

            // if (!in_array($requestedCourier, $activeCouriers, true)) {
            //     throw new \Exception('Kurir yang dipilih tidak lagi tersedia.');
            // }

            // Determine Origin and Destination based on Provider
            $origin = null;
            $destination = null;
            $provider = null;

            if ($shippingProvider === 'api_id') {
                $origin = $integrationService->get('api_id_origin_id');
                // Check if village_id is sent, otherwise use subdistrict_id but it might fail if ID types differ
                // The frontend should send the correct ID in subdistrict_id field or we add village_id field
                // For now, assume subdistrict_id field holds the Village ID for API ID
                $destination = $request->shipping_address['village_id'] ?? $request->shipping_address['subdistrict_id'];
                $provider = $this->shippingService->getProvider('api_id');
            } else {
                $origin = $integrationService->get('rajaongkir_origin_id');
                $destination = $request->shipping_address['subdistrict_id'];
                $provider = $this->shippingService->getProvider('rajaongkir');
            }
            
            if (!$origin) {
                 throw new \Exception("Store origin configuration missing.");
            }

            $costs = $provider->getCost(
                $origin,
                $destination,
                $totalWeight,
                $request->shipping_service['courier']
            );
            
            $verifiedCost = null;
            $selectedService = $request->shipping_service['service'];

            // Iterate through results to find the matching service
            foreach ($costs as $courierResult) {
                if (isset($courierResult['costs'])) {
                    foreach ($courierResult['costs'] as $c) {
                        if ($c['service'] === $selectedService) {
                            $verifiedCost = $c['cost'][0]['value'];
                            break 2;
                        }
                    }
                }
            }

            if ($verifiedCost === null) {
                // If verification fails, we might want to log it and maybe allow if close enough?
                // But for security, let's reject.
                // However, API results might vary slightly? Usually not for fixed services.
                // Let's check if we found ANY costs.
                if (empty($costs)) {
                     throw new \Exception("Shipping service verification failed (API error).");
                }
                throw new \Exception("Selected shipping service is no longer available or invalid.");
            }

            $shippingCost = $verifiedCost;

            // Add Packing Fee
            $packingFee = (int) $integrationService->get('shipping_packing_fee', 0);
            $shippingCost += $packingFee;
            
            // Tax Calculation (If applicable)
            // For MVP, we default to 0 or use config if set.
            $taxAmount = 0; 
            // Example: $taxAmount = $subtotal * 0.11;

            // Insurance Calculation (Mandatory if Active)
            $insuranceAmount = 0;
            $insuranceActive = (bool) $integrationService->get('shipping_insurance_active', 0);
            
            if ($insuranceActive) {
                $insurancePercentage = (float) $integrationService->get('shipping_insurance_percentage', 0);
                $insuranceAmount = $subtotal * ($insurancePercentage / 100);
            }

            // Unique Code Logic
            $uniqueCodeActive = (bool) $integrationService->get('store_payment_unique_code_active', 0);
            $uniqueCode = 0;
            if ($uniqueCodeActive) {
                $uniqueCode = session('checkout_unique_code', rand(1, 999));
            }

            $expiresAt = now()->addDay(); // 24 hours timeout

            $order = $this->orderService->createOrder(
                $user,
                $itemsData,
                $request->shipping_address,
                $request->shipping_service,
                $request->payment_method,
                $subtotal,
                $shippingCost,
                $taxAmount,
                $request->notes,
                $uniqueCode,
                $expiresAt,
                $insuranceAmount
            );
            
            // Clear unique code from session
            session()->forget('checkout_unique_code');
            
            // Clear the user's cart after successful order creation
            $user->cart()->delete();

            $redirectUrl = route('silverchannel.checkout.order-received', ['order' => $order->id, 'key' => $order->order_key]);

            return response()->json([
                'success' => true, 
                'message' => 'Order created successfully.', 
                'redirect_url' => $redirectUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Order creation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
