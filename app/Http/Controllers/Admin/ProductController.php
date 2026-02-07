<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\EpiProductMapping;
use App\Services\ProductService;
use App\Services\EpiAutoPriceService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;
    protected $epiService;

    public function __construct(ProductService $productService, EpiAutoPriceService $epiService)
    {
        $this->productService = $productService;
        $this->epiService = $epiService;
    }

    public function index()
    {
        $products = Product::with(['brand', 'category'])->latest()->paginate(10);
        $lastPriceUpdate = EpiProductMapping::max('last_synced_at');
        return view('admin.products.index', compact('products', 'lastPriceUpdate'));
    }

    public function create()
    {
        $brands = Brand::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('brands', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'price_msrp' => 'nullable|numeric|min:0',
            'price_customer' => 'nullable|numeric|min:0',
            'weight' => 'required|integer|min:0',
            'price_silverchannel' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $this->productService->saveProduct($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $brands = Brand::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $product->load(['stockLogs' => function($q) {
            $q->latest();
        }, 'stockLogs.user']);
        
        return view('admin.products.edit', compact('product', 'brands', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'price_msrp' => 'nullable|numeric|min:0',
            'price_customer' => 'nullable|numeric|min:0',
            'weight' => 'required|integer|min:0',
            'price_silverchannel' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $this->productService->saveProduct($validated, $product);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function updateActive(Request $request, Product $product)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $updated = $this->productService->setActiveStatus($product, (bool)$validated['is_active']);

        return response()->json([
            'status' => 'ok',
            'id' => $updated->id,
            'is_active' => (bool)$updated->is_active,
        ]);
    }

    public function syncPrice(Product $product)
    {
        try {
            // Check if product has mapping
            if (!$product->epiMapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not mapped to EPI APE.',
                ]);
            }

            $result = $this->epiService->syncProductPrice($product);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Unknown error occurred during sync.',
                ], 500);
            }

            // Refresh product to get latest values from DB
            $product->refresh();

            return response()->json([
                'success' => true,
                'price_silverchannel' => $product->price_silverchannel,
                'price_customer' => $product->price_customer,
                'last_synced_at' => $product->epiMapping->last_synced_at,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
