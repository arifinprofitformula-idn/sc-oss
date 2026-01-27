<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStockLog;
use Illuminate\Support\Facades\Auth;
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
            $product = Product::create($data);

            // Log Initial Stock
            if (isset($data['stock']) && $data['stock'] != 0) {
                $this->logStockChange($product, (int)$data['stock'], (int)$data['stock'], 'initial', 'Initial stock creation');
            }

            return $product;
        }

        // Check for stock change before update
        $oldStock = $product->stock;
        $newStock = isset($data['stock']) ? (int)$data['stock'] : $oldStock;

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
        }

        $product->update($data);

        if ($newStock !== $oldStock) {
            $diff = $newStock - $oldStock;
            $this->logStockChange($product, $diff, $newStock, 'manual_adjustment', 'Updated via admin panel');
        }

        return $product;
    }

    /**
     * Adjust Stock
     */
    public function adjustStock(Product $product, int $quantity, string $type = 'manual_adjustment', ?string $note = null, ?array $meta = null): Product
    {
        // Positive quantity adds stock, negative removes stock
        $oldStock = $product->stock;
        $newStock = $oldStock + $quantity;
        
        $product->stock = $newStock;
        $product->save();
        
        $this->logStockChange($product, $quantity, $newStock, $type, $note, $meta);
        
        return $product;
    }

    /**
     * Log Stock Change
     */
    protected function logStockChange(Product $product, int $quantity, int $finalStock, string $type, ?string $note = null, ?array $meta = null)
    {
        $product->stockLogs()->create([
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => $quantity,
            'final_stock' => $finalStock,
            'note' => $note,
            'meta' => $meta,
        ]);
    }
}
