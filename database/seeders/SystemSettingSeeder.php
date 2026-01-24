<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // RajaOngkir
            [
                'key' => 'rajaongkir_api_key',
                'value' => '',
                'group' => 'rajaongkir',
                'type' => 'encrypted',
                'description' => 'API Key from RajaOngkir',
            ],
            [
                'key' => 'rajaongkir_type',
                'value' => 'starter',
                'group' => 'rajaongkir',
                'type' => 'text',
                'description' => 'Account Type (starter, basic, pro)',
            ],
            [
                'key' => 'rajaongkir_base_url',
                'value' => 'https://rajaongkir.komerce.id/api/v1',
                'group' => 'rajaongkir',
                'type' => 'text',
                'description' => 'Base URL API RajaOngkir V2',
            ],
            [
                'key' => 'rajaongkir_origin_id',
                'value' => '',
                'group' => 'rajaongkir',
                'type' => 'text',
                'description' => 'Store Origin Location ID (Subdistrict ID)',
            ],
            [
                'key' => 'rajaongkir_origin_label',
                'value' => '',
                'group' => 'rajaongkir',
                'type' => 'text',
                'description' => 'Store Origin Location Name',
            ],
            [
                'key' => 'rajaongkir_couriers',
                'value' => 'jne,sicepat,jnt',
                'group' => 'rajaongkir',
                'type' => 'text',
                'description' => 'Active Couriers (comma separated)',
            ],
            [
                'key' => 'rajaongkir_active',
                'value' => '0',
                'group' => 'rajaongkir',
                'type' => 'boolean',
                'description' => 'Enable/Disable RajaOngkir Integration',
            ],

            // Payment Gateway (Midtrans)
            [
                'key' => 'payment_gateway_provider',
                'value' => 'midtrans',
                'group' => 'payment_gateway',
                'type' => 'text',
                'description' => 'Active Payment Gateway Provider',
            ],
            [
                'key' => 'midtrans_merchant_id',
                'value' => '',
                'group' => 'payment_gateway',
                'type' => 'text',
                'description' => 'Midtrans Merchant ID',
            ],
            [
                'key' => 'midtrans_server_key',
                'value' => '',
                'group' => 'payment_gateway',
                'type' => 'encrypted',
                'description' => 'Midtrans Server Key',
            ],
            [
                'key' => 'midtrans_client_key',
                'value' => '',
                'group' => 'payment_gateway',
                'type' => 'text',
                'description' => 'Midtrans Client Key',
            ],
            [
                'key' => 'midtrans_is_production',
                'value' => '0',
                'group' => 'payment_gateway',
                'type' => 'boolean',
                'description' => 'Production Mode (1=Yes, 0=No)',
            ],
            // Brevo (Email Marketing)
            [
                'key' => 'brevo_api_key',
                'value' => '',
                'group' => 'brevo',
                'type' => 'encrypted',
                'description' => 'API Key from Brevo',
            ],
            [
                'key' => 'brevo_sender_email',
                'value' => 'no-reply@example.com',
                'group' => 'brevo',
                'type' => 'text',
                'description' => 'Default Sender Email',
            ],
            [
                'key' => 'brevo_sender_name',
                'value' => 'EPI Admin',
                'group' => 'brevo',
                'type' => 'text',
                'description' => 'Default Sender Name',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
