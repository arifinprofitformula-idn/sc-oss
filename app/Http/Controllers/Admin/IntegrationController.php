<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IntegrationService;
use App\Services\RajaOngkirService;
use App\Services\ApiIdService;
use App\Services\ShippingService;
use App\Services\EpiAutoPriceService;
use App\Services\Email\EmailService;
use App\Models\IntegrationLog;
use App\Models\IntegrationError;
use App\Models\EmailLog;
use App\Models\Store;
use App\Models\Product;
use App\Models\EpiProductMapping;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class IntegrationController extends Controller
{
    protected $integrationService;
    protected $rajaOngkirService;
    protected $apiIdService;
    protected $shippingService;
    protected $epiAutoPriceService;
    protected $emailService;

    public function __construct(
        IntegrationService $integrationService, 
        RajaOngkirService $rajaOngkirService,
        ApiIdService $apiIdService,
        ShippingService $shippingService,
        EpiAutoPriceService $epiAutoPriceService,
        EmailService $emailService
    )
    {
        $this->integrationService = $integrationService;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->apiIdService = $apiIdService;
        $this->shippingService = $shippingService;
        $this->epiAutoPriceService = $epiAutoPriceService;
        $this->emailService = $emailService;
    }

    public function epiApe(Request $request)
    {
        $settings = $this->epiAutoPriceService->getSettings();
        
        $logs = IntegrationLog::where('integration', 'epi_ape')->latest()->take(20)->get();
        
        // Fetch Integration Errors with filtering
        $errorQuery = IntegrationError::where('integration', 'epi_ape')->latest();
        
        if ($request->has('error_status') && $request->error_status != '') {
            $errorQuery->where('status', $request->error_status);
        }
        
        $integrationErrors = $errorQuery->paginate(10);
        
        // Filter active products only
        $products = Product::with('epiMapping')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Log inactive products exclusion
        $inactiveCount = Product::where('is_active', false)->count();
        if ($inactiveCount > 0) {
            Log::info("EPI APE View: Filtered out {$inactiveCount} inactive products from mapping list.");
        }
        
        $apiStructure = [];
        try {
            if ($settings['active'] && $settings['api_key']) {
                $apiStructure = $this->epiAutoPriceService->fetchAllPrices();
            }
        } catch (\Exception $e) {
            // Ignore error for view rendering
        }

        return view('admin.integrations.epi-ape', compact('settings', 'logs', 'products', 'apiStructure', 'integrationErrors'));
    }

    public function testEpiApe(Request $request)
    {
        $result = $this->epiAutoPriceService->testConnection();
        return response()->json($result);
    }

    public function syncEpiApe(Request $request)
    {
        try {
            $result = $this->epiAutoPriceService->syncPrices();
            
            $msg = "Sync completed. Updated: " . $result['updated'] . ".";
            $errorCount = $result['error_count'] ?? 0;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'updated_count' => $result['updated'],
                    'error_count' => $errorCount,
                    'errors' => $result['errors']
                ]);
            }

            if (!empty($result['errors'])) {
                $msg .= " Errors: " . count($result['errors']);
                return back()->with('warning', $msg);
            }
            
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync failed: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function resolveEpiApeError(Request $request, $id)
    {
        $error = IntegrationError::findOrFail($id);
        $error->status = 'resolved';
        $error->save();
        
        return back()->with('success', 'Error marked as resolved.');
    }

    public function exportEpiApeErrors()
    {
        $errors = IntegrationError::where('integration', 'epi_ape')->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=epi_ape_errors.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $callback = function() use($errors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Code', 'Message', 'Details', 'Status', 'Recommendation', 'Timestamp']);
            
            foreach ($errors as $error) {
                fputcsv($file, [
                    $error->id,
                    $error->error_code,
                    $error->message,
                    json_encode($error->details),
                    $error->status,
                    $error->recommended_action,
                    $error->created_at
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function updateEpiMapping(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'epi_brand_id' => 'required|integer',
            'epi_level_id' => 'required|integer',
            'epi_level_id_customer' => 'nullable|integer',
            'epi_gramasi' => 'required|numeric|min:0.001',
            'is_active' => 'boolean'
        ]);

        EpiProductMapping::updateOrCreate(
            ['product_id' => $request->product_id],
            [
                'epi_brand_id' => $request->epi_brand_id,
                'epi_level_id' => $request->epi_level_id,
                'epi_level_id_customer' => $request->epi_level_id_customer,
                'epi_gramasi' => $request->epi_gramasi,
                'is_active' => $request->has('is_active')
            ]
        );

        // Trigger real-time sync for this product
        $product = Product::find($request->product_id);
        $syncResult = $this->epiAutoPriceService->syncProductPrice($product);
        
        $msg = 'Mapping updated successfully.';
        if($syncResult['success']) {
             $msg .= ' Price synced.';
        } else {
             $msg .= ' Sync warning: ' . $syncResult['message'];
        }

        return back()->with('success', $msg);
    }

    public function previewEpiPrice(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|integer',
            'level_id' => 'required|integer',
            'gramasi' => 'required|numeric|min:0.001',
            'product_id' => 'nullable|exists:products,id',
            'price_type' => 'nullable|in:Silverchannel,Customer'
        ]);

        $result = $this->epiAutoPriceService->getPricePreview(
            $request->brand_id,
            $request->level_id,
            $request->gramasi
        );

        // Update product price if product_id is provided (Real-time sync on check)
        if ($result['success'] && $request->product_id && $request->price_type) {
            $product = \App\Models\Product::find($request->product_id);
            if ($product) {
                if ($request->price_type === 'Silverchannel') {
                    $product->price_silverchannel = $result['price'];
                } elseif ($request->price_type === 'Customer') {
                    $product->price_customer = $result['price'];
                }
                $product->save();
            }
        }

        return response()->json($result);
    }

    public function deleteEpiMapping($id)
    {
        EpiProductMapping::destroy($id);
        return back()->with('success', 'Mapping removed successfully.');
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

    public function email(Request $request)
    {
        $brevoSettings = $this->integrationService->getAll('brevo');
        $mailketingSettings = $this->integrationService->getAll('mailketing');
        $emailSettings = $this->integrationService->getAll('email');
        $routingSettings = $this->integrationService->getAll('email_routing');
        
        $settings = array_merge($brevoSettings, $mailketingSettings, $emailSettings, $routingSettings);

        $logs = \App\Models\IntegrationLog::whereIn('integration', ['brevo', 'mailketing'])
            ->latest()
            ->take(20)
            ->get();

        $testEmailLogs = EmailLog::where('type', 'test_email')
            ->latest()
            ->take(5)
            ->get();

        // Email Templates Logic
        $query = EmailTemplate::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%");
            });
        }

        $templates = $query->latest()->paginate(10);

        return view('admin.integrations.email', compact('settings', 'logs', 'templates', 'testEmailLogs'));
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $this->emailService->sendRaw(
                $request->email,
                $request->subject,
                $request->message
            );

            return response()->json([
                'success' => true,
                'message' => "Email berhasil dikirim ke {$request->email}"
            ]);
        } catch (\Exception $e) {
            Log::error('Test Email Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            $suggestion = 'Check SMTP configuration and internet connection.';
            if (str_contains($e->getMessage(), 'Connection could not be established')) {
                $suggestion = 'Cannot connect to SMTP server. Verify host and port.';
            } elseif (str_contains($e->getMessage(), 'Failed to authenticate')) {
                $suggestion = 'Authentication failed. Verify username and password.';
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
                'suggestion' => $suggestion
            ], 500);
        }
    }

    public function testBrevo()
    {
        $result = $this->integrationService->testBrevoConnection();
        return response()->json($result);
    }

    public function testMailketing()
    {
        // Simple test send to configured sender email
        $provider = new \App\Services\Email\MailketingProvider();
        $senderEmail = \App\Models\SystemSetting::getValue('mailketing_sender_email');
        
        if (!$senderEmail) {
            return response()->json(['success' => false, 'message' => 'Sender Email not configured']);
        }

        $result = $provider->sendEmail(
            $senderEmail,
            'Test Email from EPI OSS',
            '<h1>It Works!</h1><p>This is a test email from Mailketing integration.</p>'
        );

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
            'epi_ape_api_key' => 'nullable|string',
            'epi_ape_base_url' => 'nullable|url',
            'epi_ape_update_interval' => 'nullable|integer|min:5',
            'epi_ape_notify_email' => 'nullable|email',
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
                if ($key === 'brevo_smtp_login') $type = 'text'; // Added this line
            } elseif (str_contains($key, 'mailketing')) {
                $group = 'mailketing';
                if ($key === 'mailketing_api_token') $type = 'encrypted';
            } elseif (str_starts_with($key, 'email_route_')) {
                $group = 'email_routing';
                $type = 'text';
            } elseif (str_contains($key, 'epi_ape')) {
                $group = 'epi_ape';
                if ($key === 'epi_ape_active') $type = 'boolean';
                if ($key === 'epi_ape_api_key') $type = 'encrypted';
                if ($key === 'epi_ape_update_interval') $type = 'integer';
            }

            // Handle array values (like couriers list)
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif (is_string($value)) {
                $value = trim($value);
            }

            // Skip updating encrypted fields if value is empty (to keep existing)
            if ($type === 'encrypted' && empty($value)) {
                continue;
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
        if (!$request->has('epi_ape_active') && $request->has('epi_ape_api_key')) {
            $this->integrationService->set('epi_ape_active', 0, 'epi_ape', 'boolean');
        }

        if ($rajaOngkirChanged) {
            Cache::forget('rajaongkir_provinces');
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
