<?php

namespace Tests\Unit\Imports;

use App\Imports\ProductImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    protected $brand;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->brand = Brand::factory()->create(['name' => 'Silvergram']);
        $this->category = Category::factory()->create(['name' => 'Logam Mulia']);
    }

    public function test_model_throws_exception_if_category_missing()
    {
        $import = new ProductImport();
        
        $row = [
            'sku' => 'TEST-001',
            'nama_produk' => 'Test Product',
            'brand' => 'Silvergram',
            'kategori' => 'NonExistentCategory',
            'harga_silverchannel' => 100000,
            'stok' => 10,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Kategori 'NonExistentCategory' tidak ditemukan di sistem");

        $import->model($row);
    }

    public function test_model_throws_exception_if_brand_missing()
    {
        $import = new ProductImport();
        
        $row = [
            'sku' => 'TEST-002',
            'nama_produk' => 'Test Product',
            'brand' => 'NonExistentBrand',
            'kategori' => 'Logam Mulia',
            'harga_silverchannel' => 100000,
            'stok' => 10,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Brand 'NonExistentBrand' tidak ditemukan di sistem");

        $import->model($row);
    }

    public function test_model_returns_product_if_valid()
    {
        $import = new ProductImport();
        
        $row = [
            'sku' => 'TEST-003',
            'nama_produk' => 'Test Product Valid',
            'brand' => 'Silvergram',
            'kategori' => 'Logam Mulia',
            'harga_silverchannel' => 100000,
            'msrp' => 120000,
            'berat_gram' => 5,
            'stok' => 10,
        ];

        $product = $import->model($row);

        $this->assertNotNull($product);
        $this->assertEquals('TEST-003', $product->sku);
        $this->assertEquals($this->brand->id, $product->brand_id);
        $this->assertEquals($this->category->id, $product->category_id);
        $this->assertEquals('test-product-valid', $product->slug);
    }

    public function test_model_generates_unique_slug_if_exists_in_db()
    {
        // Create existing product
        Product::factory()->create([
            'name' => 'Existing Product',
            'slug' => 'existing-product',
            'sku' => 'EXIST-001'
        ]);

        $import = new ProductImport();
        
        $row = [
            'sku' => 'NEW-001',
            'nama_produk' => 'Existing Product', // Same name
            'brand' => 'Silvergram',
            'kategori' => 'Logam Mulia',
            'harga_silverchannel' => 100000,
            'stok' => 10,
        ];

        $product = $import->model($row);

        $this->assertNotNull($product);
        $this->assertEquals('existing-product-1', $product->slug);
    }

    public function test_model_generates_unique_slug_in_batch()
    {
        $import = new ProductImport();
        
        $row1 = [
            'sku' => 'BATCH-001',
            'nama_produk' => 'Batch Product',
            'brand' => 'Silvergram',
            'kategori' => 'Logam Mulia',
            'harga_silverchannel' => 100000,
            'stok' => 10,
        ];

        $row2 = [
            'sku' => 'BATCH-002',
            'nama_produk' => 'Batch Product', // Same name in same batch
            'brand' => 'Silvergram',
            'kategori' => 'Logam Mulia',
            'harga_silverchannel' => 100000,
            'stok' => 10,
        ];

        $product1 = $import->model($row1);
        $product2 = $import->model($row2);

        $this->assertEquals('batch-product', $product1->slug);
        $this->assertEquals('batch-product-1', $product2->slug);
    }
}
