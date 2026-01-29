<?php

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\Product;
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

        if ($request->wantsJson()) {
            return response()->json([
                'operational_status' => $operationalStatus,
                'products' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        }

        return view('silverchannel.products.index', compact('products', 'operationalStatus'));
    }
}
