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
        $storeId = $this->getPrimaryStoreId();

        if (!$storeId) {
            return $this->defaultOpenStatus();
        }

        // Cache the CONFIGURATION for 1 hour. 
        // Real-time status is calculated against this config.
        $cacheKey = 'store_operational_config_' . $storeId;
        $config = Cache::remember($cacheKey, 3600, function () use ($storeId) {
            $store = Store::find($storeId);
            if (!$store) return null;
            return [
                'is_open' => (bool) $store->is_open,
                'holiday_mode' => (bool) $store->holiday_mode,
                'holiday_note' => $store->holiday_note,
                'operating_hours' => $store->operating_hours,
            ];
        });

        if (!$config) {
            return $this->defaultOpenStatus();
        }

        return $this->calculateStatusFromConfig($config);
    }

    public function refreshStatus(): array
    {
        $storeId = $this->getPrimaryStoreId();
        if ($storeId) {
            Cache::forget('store_operational_config_' . $storeId);
            Log::info('Store operational config cache cleared', ['store_id' => $storeId]);
        }
        return $this->getStatus();
    }

    protected function getPrimaryStoreId(): ?int
    {
        // Cache primary store ID resolution for 1 hour
        return Cache::remember('silverchannel_primary_store_id_resolved', 3600, function() {
            try {
                /** @var IntegrationService $integration */
                $integration = app(IntegrationService::class);
                $primaryId = $integration->get('silverchannel_primary_store_id');
    
                if ($primaryId) {
                    return (int) $primaryId;
                }
            } catch (\Throwable $e) {
                Log::error('Failed to resolve primary store from settings', [
                    'message' => $e->getMessage(),
                ]);
            }
    
            $store = Store::first();
            return $store ? $store->id : null;
        });
    }

    protected function calculateStatusFromConfig(array $config): array
    {
        $schedule = $config['operating_hours'] ?? [];
        $now = Carbon::now('Asia/Jakarta'); // TODO: Make timezone configurable via settings

        // Priority 1: Manual Close (Toggle) or Holiday Mode
        if ($config['holiday_mode']) {
            $reason = !empty($config['holiday_note']) ? $config['holiday_note'] : 'holiday_mode';
            return $this->buildStatusResponse('CLOSED', false, $schedule, $reason, $now);
        }

        if (!$config['is_open']) {
            return $this->buildStatusResponse('CLOSED', false, $schedule, 'store_closed_toggle', $now);
        }

        if (!is_array($schedule) || empty($schedule)) {
            // Default to open if no schedule defined
            return $this->buildStatusResponse('OPEN', true, $schedule, null, $now);
        }

        // Priority 2: Schedule Check
        $dayName = strtolower($now->format('l')); // monday, tuesday, etc.
        
        if (!isset($schedule[$dayName])) {
             return $this->buildStatusResponse('OPEN', true, $schedule, 'schedule_missing_for_day', $now);
        }

        $todaySchedule = $schedule[$dayName];

        // Check if closed for the day
        if (isset($todaySchedule['is_closed']) && $todaySchedule['is_closed']) {
            return $this->buildStatusResponse('CLOSED', false, $schedule, 'closed_today', $now);
        }

        // Check time range
        $openTime = $todaySchedule['open'] ?? '00:00';
        $closeTime = $todaySchedule['close'] ?? '23:59';
        
        $currentTimeStr = $now->format('H:i');
        
        if ($currentTimeStr < $openTime || $currentTimeStr >= $closeTime) {
             return array_merge(
                 $this->buildStatusResponse('CLOSED', false, $schedule, 'outside_hours', $now),
                 ['open_time' => $openTime, 'close_time' => $closeTime]
             );
        }

        return $this->buildStatusResponse('OPEN', true, $schedule, null, $now);
    }

    protected function buildStatusResponse(string $status, bool $isOpen, ?array $schedule, ?string $reason, Carbon $now): array
    {
        return [
            'status' => $status,
            'is_open' => $isOpen,
            'schedule' => $schedule,
            'can_add_to_cart' => $isOpen,
            'reason' => $reason,
            'current_time' => $now->format('H:i'),
            'current_day' => strtolower($now->format('l')),
        ];
    }

    protected function defaultOpenStatus(): array
    {
        return [
            'status' => 'OPEN',
            'is_open' => true,
            'schedule' => null,
            'can_add_to_cart' => true,
            'reason' => null,
            'current_time' => Carbon::now('Asia/Jakarta')->format('H:i'),
            'current_day' => strtolower(Carbon::now('Asia/Jakarta')->format('l')),
        ];
    }
}
