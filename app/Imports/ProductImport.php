<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ProductImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $updateExisting;
    protected $brands;
    protected $categories;
    protected $rowCount = 0;
    protected $generatedSlugs = [];

    public function __construct($updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
        $this->brands = Brand::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $this->categories = Category::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
    }

    public function model(array $row)
    {
        $this->rowCount++;

        $sku = trim($row['sku']);
        $product = Product::withTrashed()->where('sku', $sku)->first();

        if ($product && !$this->updateExisting) {
            return null; // Skip if exists and not updating
        }

        // Resolve Brand
        $brandName = trim($row['brand'] ?? '');
        $brandId = $this->brands[strtolower($brandName)] ?? null;
        if (!$brandId && $brandName) {
            // Strict check: if brand name provided but not found, fail.
            // If empty brand name, let validation handle it (nullable rule).
             throw new \Exception("Brand '{$brandName}' tidak ditemukan di sistem. Pastikan penulisan sesuai.");
        }

        // Resolve Category
        $categoryName = trim($row['kategori'] ?? '');
        $categoryId = $this->categories[strtolower($categoryName)] ?? null;

        if (!$categoryId) {
             throw new \Exception("Kategori '{$categoryName}' tidak ditemukan di sistem. Pastikan penulisan sesuai.");
        }

        // Generate Unique Slug
        $baseSlug = Str::slug($row['nama_produk']);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $product->id ?? null)) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $this->generatedSlugs[] = $slug;

        $data = [
            'name' => $row['nama_produk'],
            'slug' => $slug,
            'sku' => $sku,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'price_silverchannel' => $row['harga_silverchannel'],
            'price_msrp' => $row['msrp'] ?? 0,
            'weight' => $row['berat_gram'] ?? 0,
            'stock' => $row['stok'],
            'description' => $row['deskripsi'] ?? null,
            'image' => $row['url_gambar'] ?? null, // Assuming URL, we might need to download? 
            // The prompt says "format gambar URL harus valid", so we store the URL?
            // Product model usually stores path. If it's a URL, we might need to store it as is if supported,
            // or download it. For MVP, we store the string.
            'is_active' => true,
        ];

        if ($product) {
            $product->update($data);
            if ($product->trashed()) {
                $product->restore();
            }
            return null; // Return null to avoid batch insert issues with updates
        }

        return new Product($data);
    }

    public function rules(): array
    {
        return [
            'sku' => ['required'],
            'nama_produk' => ['required'],
            'harga_silverchannel' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'brand' => ['nullable'], // Could add 'exists:brands,name' but case sensitivity issues
            'kategori' => ['required'],
            'url_gambar' => ['nullable', 'url'],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    private function slugExists($slug, $ignoreId = null)
    {
        // Check in-memory generated slugs (for current batch)
        if (in_array($slug, $this->generatedSlugs)) {
            return true;
        }

        // Check database
        $query = Product::withTrashed()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        return $query->exists();
    }
}
