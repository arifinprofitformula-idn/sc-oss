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
                'benefits' => json_encode(['Akses Produk Silver', 'Komisi Referral', 'Support Prioritas']), // Casted to array in model likely, but let's be safe
                'description' => 'Paket pendaftaran standar untuk menjadi Silverchannel resmi.',
                'is_active' => true,
            ]);
        }
    }
}
