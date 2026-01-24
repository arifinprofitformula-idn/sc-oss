<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create or Update Brand
     */
    public function saveBrand(array $data, ?Brand $brand = null): Brand
    {
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['logo'];
            $extension = $file->getClientOriginalExtension();
            $filename = 'brand_logos/' . Str::uuid() . '.' . $extension;

            if (!Storage::disk('public')->put($filename, $file->get())) {
                 throw new \Exception('Failed to store brand logo.');
            }

            if ($brand && !empty($brand->logo) && is_string($brand->logo) && trim($brand->logo) !== '') {
                if (Storage::disk('public')->exists($brand->logo)) {
                    Storage::disk('public')->delete($brand->logo);
                }
            }
            $data['logo'] = $filename;
        }

        if (!$brand) {
            $data['slug'] = Str::slug($data['name']);
            return Brand::create($data);
        }

        if (isset($data['name']) && $data['name'] !== $brand->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $brand->update($data);
        return $brand;
    }

    /**
     * Create or Update Product
     */
    public function saveProduct(array $data, ?Product $product = null): Product
    {
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['image'];
            $extension = $file->getClientOriginalExtension();
            // Generate robust filename
            $filename = 'product_images/' . date('Y/m') . '/' . Str::uuid() . '.' . $extension;

            // Manual put to ensure path control
            if (!Storage::disk('public')->put($filename, $file->get())) {
                 throw new \Exception('Failed to store product image.');
            }

            // Cleanup old image only if new one saved successfully
            if ($product && !empty($product->image) && is_string($product->image) && trim($product->image) !== '') {
                if (Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
            }
            
            $data['image'] = $filename;
        }

        if (!$product) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
            return Product::create($data);
        }

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
        }

        $product->update($data);
        return $product;
    }

    /**
     * Adjust Stock
     */
    public function adjustStock(Product $product, int $quantity, string $reason = 'adjustment'): Product
    {
        // Positive quantity adds stock, negative removes stock
        $product->increment('stock', $quantity);
        
        // TODO: Log stock history here if needed later
        
        return $product;
    }
}
