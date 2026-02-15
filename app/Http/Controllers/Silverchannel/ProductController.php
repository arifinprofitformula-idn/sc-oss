<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\EpiProductMapping;
use App\Services\StoreOperationalService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected StoreOperationalService $storeOperationalService;

    public function __construct(StoreOperationalService $storeOperationalService)
    {
        $this->storeOperationalService = $storeOperationalService;
    }

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category'])->where('is_active', true);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('brand')) {
            $query->where('brand_id', $request->brand);
        }

        $products = $query->paginate(12);

        $operationalStatus = $this->storeOperationalService->getStatus();
        $lastPriceUpdate = EpiProductMapping::max('last_synced_at');

        if ($request->wantsJson()) {
            return response()->json([
                'operational_status' => $operationalStatus,
                'products' => $products->items(),
                'last_price_update' => $lastPriceUpdate,
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        }

        return view('silverchannel.products.index', compact('products', 'operationalStatus', 'lastPriceUpdate'));
    }

    public function checkPrices(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $products = Product::whereIn('id', $request->ids)->get(['id', 'price_customer', 'price_silverchannel']);

        $data = $products->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'price_customer' => (float) $product->price_customer,
                    'price_silverchannel' => (float) $product->price_silverchannel,
                    'formatted_customer' => 'Rp ' . number_format($product->price_customer, 0, ',', '.'),
                    'formatted_silverchannel' => 'Rp ' . number_format($product->price_silverchannel, 0, ',', '.'),
                    'has_customer_price' => $product->price_customer > 0,
                ]
            ];
        });

        return response()->json($data);
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }
        return view('silverchannel.products.show', compact('product'));
    }
}
