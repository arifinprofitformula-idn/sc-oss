<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductPricelistExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Nama Produk',
            'Kategori',
            'Berat (g)',
            'Stok',
            'Harga Konsumen (MSRP)',
            'Harga Silverchannel',
            'Terakhir Update'
        ];
    }

    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->category ? $product->category->name : '-',
            $product->weight,
            $product->stock,
            $product->price_customer,
            $product->price_silverchannel,
            $product->updated_at ? $product->updated_at->format('d-m-Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
