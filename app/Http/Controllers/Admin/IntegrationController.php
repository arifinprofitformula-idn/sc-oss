<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IntegrationService;
use App\Services\RajaOngkirService;
use App\Services\ApiIdService;
use App\Services\ShippingService;
use App\Models\IntegrationLog;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class IntegrationController extends Controller
{
    protected $integrationService;
    protected $rajaOngkirService;
    protected $apiIdService;
    protected $shippingService;

    public function __construct(
        IntegrationService $integrationService, 
        RajaOngkirService $rajaOngkirService,
        ApiIdService $apiIdService,
        ShippingService $shippingService
    )
    {
        $this->integrationService = $integrationService;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->apiIdService = $apiIdService;
        $this->shippingService = $shippingService;
    }

    public function index()
    {
        return redirect()->route('admin.integrations.shipping');
    }

    public function shipping()
    {
        $activeProvider = $this->integrationService->get('shipping_provider', 'rajaongkir');

        $insuranceSettings = [
            'active' => $this->integrationService->get('shipping_insurance_active', 0),
            'percentage' => $this->integrationService->get('shipping_insurance_percentage', 0),
            'description' => $this->integrationService->get('shipping_insurance_description', 'Biaya asuransi pengiriman'),
            'packing_fee' => $this->integrationService->get('shipping_packing_fee', 0),
        ];

        $rajaOngkirSettings = [
            'api_key' => $this->integrationService->get('rajaongkir_api_key'),
            'base_url' => $this->integrationService->get('rajaongkir_base_url'),
            'type' => $this->integrationService->get('rajaongkir_type'),
            'active' => $this->integrationService->get('rajaongkir_active'),
            'origin_id' => $this->integrationService->get('rajaongkir_origin_id'),
            'origin_label' => $this->integrationService->get('rajaongkir_origin_label'),
            'couriers' => $this->integrationService->get('rajaongkir_couriers'),
        ];

        $apiIdSettings = [
            'api_key' => $this->integrationService->get('api_id_key'),
            'base_url' => $this->integrationService->get('api_id_base_url'),
            'active' => $this->integrationService->get('api_id_active'),
            'origin_id' => $this->integrationService->get('api_id_origin_id'),
            'origin_label' => $this->integrationService->get('api_id_origin_label'),
        ];

        // Global Active Couriers
        $activeCouriers = json_decode($this->integrationService->get('shipping_active_couriers', '[]'), true) ?? [];
        if (empty($activeCouriers)) {
            // Default to common ones if not set
            $activeCouriers = ['jne', 'pos', 'tiki', 'sicepat', 'jnt'];
        }

        // Available Couriers List (Supported by System)
        $availableCouriers = ['jne', 'pos', 'tiki', 'sicepat', 'jnt', 'anteraja', 'lion', 'ninja', 'wahana', 'rpx', 'pahala', 'esl', 'pcp', 'pandu', 'sap', 'jet', 'dse', 'first', 'star', 'idx', 'idl'];

        // Silverchannel Stores
        $stores = Store::with('user')->get();
        
        $logs = IntegrationLog::whereIn('integration', ['rajaongkir', 'api_id'])->latest()->take(20)->get();

        return view('admin.integrations.shipping', compact('activeProvider', 'insuranceSettings', 'rajaOngkirSettings', 'apiIdSettings', 'logs', 'activeCouriers', 'availableCouriers', 'stores'));
    }


    public function payment()
    {
        $settings = [
            'provider' => $this->integrationService->get('payment_gateway_provider'),
            'merchant_id' => $this->integrationService->get('midtrans_merchant_id'),
            'server_key' => $this->integrationService->get('midtrans_server_key'),
            'client_key' => $this->integrationService->get('midtrans_client_key'),
            'is_production' => $this->integrationService->get('midtrans_is_production'),
        ];

        $logs = IntegrationLog::where('integration', 'payment_gateway')->latest()->take(10)->get();

        return view('admin.integrations.payment', compact('settings', 'logs'));
    }

    public function brevo()
    {
        $settings = $this->integrationService->getAll('brevo');
        $logs = \App\Models\IntegrationLog::where('integration', 'brevo')
            ->latest()
            ->take(10)
            ->get();
        return view('admin.integrations.brevo', compact('settings', 'logs'));
    }

    public function testBrevo()
    {
        $result = $this->integrationService->testBrevoConnection();
        return response()->json($result);
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        // Basic Validation
        $request->validate([
            'shipping_provider' => 'nullable|in:rajaongkir,api_id',
            'shipping_insurance_percentage' => 'nullable|numeric|min:0|max:100',
            'shipping_packing_fee' => 'nullable|numeric|min:0',
            'shipping_insurance_description' => 'nullable|string|max:255',
            'rajaongkir_api_key' => 'nullable|string',
            'rajaongkir_base_url' => 'nullable|url',
            'rajaongkir_origin_id' => 'nullable|string',
            'rajaongkir_couriers' => 'nullable|array',
            'api_id_key' => 'nullable|string',
            'api_id_base_url' => 'nullable|url',
            'api_id_origin_id' => 'nullable|string',
            'api_id_origin_label' => 'nullable|string',
            'midtrans_merchant_id' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
            'midtrans_server_key' => 'nullable|string',
            'brevo_api_key' => 'nullable|string',
            'brevo_sender_email' => 'nullable|email',
        ]);

        $rajaOngkirChanged = false;

        foreach ($data as $key => $value) {
            $group = 'general';
            $type = 'text';

            if (str_starts_with($key, 'shipping_')) {
                $group = 'shipping';
                $type = 'text';
                if ($key === 'shipping_insurance_active') $type = 'boolean';
            } elseif (str_contains($key, 'rajaongkir')) {
                $group = 'rajaongkir';
                $rajaOngkirChanged = true;
                if ($key === 'rajaongkir_active') $type = 'boolean';
                if ($key === 'rajaongkir_api_key') $type = 'encrypted';
            } elseif (str_contains($key, 'api_id')) {
                $group = 'api_id';
                if ($key === 'api_id_key') $type = 'encrypted';
                if ($key === 'api_id_active') $type = 'boolean';
            } elseif (str_contains($key, 'midtrans') || str_contains($key, 'payment')) {
                $group = 'payment_gateway';
                if (str_contains($key, 'is_production')) $type = 'boolean';
                if (str_contains($key, 'server_key')) $type = 'encrypted';
            } elseif (str_contains($key, 'brevo')) {
                $group = 'brevo';
                if ($key === 'brevo_active') $type = 'boolean';
                if ($key === 'brevo_api_key') $type = 'encrypted';
            }

            // Handle array values (like couriers list)
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $this->integrationService->set($key, $value, $group, $type);
        }

        if (!$request->has('rajaongkir_active') && $request->has('rajaongkir_base_url')) {
            $this->integrationService->set('rajaongkir_active', 0, 'rajaongkir', 'boolean');
            $rajaOngkirChanged = true;
        }
        if (!$request->has('shipping_insurance_active') && $request->has('shipping_insurance_percentage')) {
            $this->integrationService->set('shipping_insurance_active', 0, 'shipping', 'boolean');
        }
        if (!$request->has('midtrans_is_production') && $request->has('midtrans_merchant_id')) {
            $this->integrationService->set('midtrans_is_production', 0, 'payment_gateway', 'boolean');
        }
        if (!$request->has('brevo_active') && $request->has('brevo_sender_email')) {
            $this->integrationService->set('brevo_active', 0, 'brevo', 'boolean');
        }

        if ($rajaOngkirChanged) {
            \Illuminate\Support\Facades\Cache::forget('rajaongkir_provinces');
        }

        // Audit Log
        Log::info('Shipping/Integration Settings Updated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'ip' => $request->ip(),
            'changes' => array_keys($data)
        ]);

        return back()->with('success', 'Configuration saved successfully. Caches cleared where applicable.');
    }

    public function testRajaOngkir(Request $request)
    {
        $result = $this->integrationService->testRajaOngkir(
            $request->input('api_key'),
            $request->input('base_url')
        );
        return response()->json($result);
    }

    public function testApiId(Request $request)
    {
        $result = $this->apiIdService->testConnection(
            $request->input('api_key'),
            $request->input('base_url')
        );
        return response()->json($result);
    }

    public function getProvinces(Request $request)
    {
        try {
            $provider = $request->input('provider');
            return response()->json($this->shippingService->getProvinces($provider));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCities(Request $request, $provinceId)
    {
        try {
            $provider = $request->input('provider');
            return response()->json($this->shippingService->getCities($provinceId, $provider));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSubdistricts(Request $request, $cityId)
    {
        try {
            $provider = $request->input('provider');
            return response()->json($this->shippingService->getSubdistricts($cityId, $provider));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getVillages(Request $request, $subdistrictId)
    {
        try {
            $provider = $request->input('provider');
            return response()->json($this->shippingService->getVillages($subdistrictId, $provider));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchDestination(Request $request)
    {
        $query = $request->get('q');
        $provider = $request->get('provider'); // Optional provider override
        
        if (strlen($query) < 3) return response()->json([]);

        $results = $this->shippingService->searchDestination($query, $provider);
        return response()->json($results);
    }

    public function testShippingCost(Request $request)
    {
        $request->validate([
            'destination_id' => 'required',
            'weight' => 'required|numeric|min:1',
            'courier' => 'required|string',
            'provider' => 'nullable|string|in:rajaongkir,api_id',
        ]);

        $activeProvider = $request->input('provider') ?: $this->integrationService->get('shipping_provider', 'rajaongkir');
        
        if ($activeProvider === 'api_id') {
            $origin = $this->integrationService->get('api_id_origin_id');
            if (!$origin) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Please save Store Origin (in API ID settings) first.'
                ], 400);
            }
        } else {
            $origin = $this->integrationService->get('rajaongkir_origin_id');
            if (!$origin) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Please save Store Origin first.'
                ], 400);
            }
        }

        try {
            // Force the provider in the service call
            $results = $this->shippingService->getCost($origin, $request->destination_id, $request->weight, $request->courier, $activeProvider);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStoreShipping(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'couriers' => 'nullable|array',
            'couriers.*' => 'string'
        ]);

        $store = Store::findOrFail($request->store_id);
        
        // Get global active couriers to validate against
        $globalCouriers = json_decode($this->integrationService->get('shipping_active_couriers', '[]'), true) ?? [];
        if (empty($globalCouriers)) {
            $legacyCouriers = (string) $this->integrationService->get('rajaongkir_couriers', '');
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

        // Filter requested couriers to only allow those that are globally active
        $requestedCouriers = $request->input('couriers', []);
        $validCouriers = array_values(array_intersect($requestedCouriers, $globalCouriers));

        $store->shipping_couriers = $validCouriers;
        $store->save();

        return back()->with('success', "Shipping configuration for {$store->name} updated successfully.");
    }

    public function docs()
    {
        return view('admin.integrations.docs');
    }
}
