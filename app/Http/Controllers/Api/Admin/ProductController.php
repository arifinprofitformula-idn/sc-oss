<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products,sku',
            'price_customer' => 'required|numeric|min:0',
            'price_silverchannel' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
            
            // Commission Fields
            'commission_enabled' => 'boolean',
            'commission_type' => 'required_if:commission_enabled,1|in:percentage,fixed',
            'commission_value' => 'required_if:commission_enabled,1|numeric|min:0',
        ]);

        try {
            $product = $this->productService->saveProduct($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'brand_id' => 'sometimes|required|exists:brands,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'sku' => 'sometimes|required|string|unique:products,sku,' . $product->id,
            'price_customer' => 'sometimes|required|numeric|min:0',
            'price_silverchannel' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'weight' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
            
            // Commission Fields
            'commission_enabled' => 'boolean',
            'commission_type' => 'required_if:commission_enabled,1|in:percentage,fixed',
            'commission_value' => 'required_if:commission_enabled,1|numeric|min:0',
        ]);

        try {
            // Handle boolean field explicit setting if present in request but not validated as true/false strictly
            if ($request->has('commission_enabled')) {
                $validated['commission_enabled'] = $request->boolean('commission_enabled');
            }

            $product = $this->productService->saveProduct($validated, $product);
            
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get commission data for a specific product.
     */
    public function getCommission(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'commission_enabled' => (bool) $product->commission_enabled,
                'commission_type' => $product->commission_type,
                'commission_value' => (float) $product->commission_value,
            ]
        ]);
    }
}
