<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class InsuranceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'insurance_percentage',
                'value' => '0.5', // 0.5%
                'group' => 'insurance',
                'type' => 'decimal',
                'description' => 'Insurance Percentage (in %)',
            ],
            [
                'key' => 'insurance_active',
                'value' => '1',
                'group' => 'insurance',
                'type' => 'boolean',
                'description' => 'Enable/Disable Insurance Calculation',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
