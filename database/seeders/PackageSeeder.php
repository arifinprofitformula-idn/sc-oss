<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if basic package exists
        if (!Package::where('name', 'Silverchannel Basic')->exists()) {
            Package::create([
                'name' => 'Silverchannel Basic',
                'price' => 500000,
                'original_price' => 750000,
                'benefits' => ['Akses Produk Silver', 'Komisi Referral', 'Support Prioritas'],
                'description' => 'Paket pendaftaran standar untuk menjadi Silverchannel resmi.',
                'is_active' => true,
            ]);
        }
    }
}
