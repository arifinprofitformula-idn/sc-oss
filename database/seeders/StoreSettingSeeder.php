<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (StoreSetting::count() === 0) {
            StoreSetting::create([
                'distributor_name' => 'EPI Official Store',
                'distributor_address' => 'Jakarta Pusat',
                'distributor_phone' => '081234567890',
                'unique_code_enabled' => true,
                'unique_code_range_start' => 1,
                'unique_code_range_end' => 999,
                'bank_info' => [
                    [
                        'bank' => 'BCA',
                        'account_number' => '1234567890',
                        'account_name' => 'PT Emas Perak Indonesia'
                    ]
                ]
            ]);
        }
    }
}
