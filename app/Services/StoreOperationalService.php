<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\IntegrationService;

class StoreOperationalService
{
    public function getStatus(): array
    {
        $store = $this->getPrimaryStore();

        if (!$store) {
            return [
                'status' => 'OPEN',
                'is_open' => true,
                'schedule' => null,
                'can_add_to_cart' => true,
                'reason' => null,
            ];
        }

        $cacheKey = 'store_operational_status_' . $store->id;

        return Cache::remember($cacheKey, 60, function () use ($store) {
            return $this->calculateStatus($store);
        });
    }

    public function refreshStatus(): array
    {
        $store = $this->getPrimaryStore();

        if (!$store) {
            return [
                'status' => 'OPEN',
                'is_open' => true,
                'schedule' => null,
                'can_add_to_cart' => true,
                'reason' => null,
            ];
        }

        $cacheKey = 'store_operational_status_' . $store->id;
        $old = Cache::get($cacheKey);

        $new = $this->calculateStatus($store);

        Cache::put($cacheKey, $new, 60);

        if ($old && isset($old['status']) && $old['status'] !== $new['status']) {
            Log::info('Store operational status changed', [
                'store_id' => $store->id,
                'from' => $old['status'],
                'to' => $new['status'],
            ]);
        }

        return $new;
    }

    protected function getPrimaryStore(): ?Store
    {
        try {
            /** @var IntegrationService $integration */
            $integration = app(IntegrationService::class);
            $primaryId = $integration->get('silverchannel_primary_store_id');

            if ($primaryId) {
                $store = Store::find($primaryId);
                if ($store) {
                    return $store;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to resolve primary store from settings', [
                'message' => $e->getMessage(),
            ]);
        }

        return Store::first();
    }


    protected function calculateStatus(Store $store): array
    {
        $schedule = $store->operating_hours;

        // Priority 1: Manual Close (Toggle)
        if (!$store->is_open) {
            return [
                'status' => 'CLOSED',
                'is_open' => false,
                'schedule' => $schedule,
                'can_add_to_cart' => false,
                'reason' => 'store_closed_toggle',
            ];
        }

        if (!is_array($schedule) || empty($schedule)) {
            return [
                'status' => 'OPEN',
                'is_open' => true,
                'schedule' => null,
                'can_add_to_cart' => true,
                'reason' => null,
            ];
        }

        return [
            'status' => 'OPEN',
            'is_open' => true,
            'schedule' => $schedule,
            'can_add_to_cart' => true,
            'reason' => null,
        ];
    }
}
