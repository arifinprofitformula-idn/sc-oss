<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Services\IntegrationService;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    private function makeService(): RajaOngkirService
    {
        $integration = \Mockery::mock(IntegrationService::class);

        $integration->shouldReceive('get')->andReturnUsing(function (string $key) {
            if ($key === 'rajaongkir_api_key') {
                return 'test_key';
            }

            if ($key === 'rajaongkir_base_url') {
                return 'https://api.rajaongkir.com/starter';
            }

            return null;
        });

        $integration->shouldReceive('log')->andReturnNull();

        return new RajaOngkirService($integration);
    }

    public function test_get_cost_uses_cache_for_repeated_requests(): void
    {
        Http::fake([
            'https://api.rajaongkir.com/*' => Http::response([
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK'],
                    'results' => [
                        [
                            'code' => 'jne',
                            'costs' => [
                                [
                                    'service' => 'REG',
                                    'cost' => [
                                        ['value' => 15000, 'etd' => '2-3', 'note' => ''],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = $this->makeService();

        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = $service->getCost('1', '2', 1000, 'jne');
        }

        $this->assertNotEmpty($results[0]);
        $this->assertSame($results[0], $results[4]);
        Http::assertSentCount(1);
    }

    public function test_get_cost_retries_on_server_error_and_succeeds(): void
    {
        Http::fake([
            'https://api.rajaongkir.com/*' => Http::sequence()
                ->push([
                    'rajaongkir' => [
                        'status' => ['code' => 500, 'description' => 'Server error'],
                        'results' => [],
                    ],
                ], 500)
                ->push([
                    'rajaongkir' => [
                        'status' => ['code' => 200, 'description' => 'OK'],
                        'results' => [
                            [
                                'code' => 'jne',
                                'costs' => [
                                    [
                                        'service' => 'REG',
                                        'cost' => [
                                            ['value' => 12000, 'etd' => '1-2', 'note' => ''],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $service = $this->makeService();

        $result = $service->getCost('1', '2', 1000, 'jne');

        $this->assertNotEmpty($result);
        Http::assertSentCount(2);
    }

    public function test_get_cost_throws_after_max_retries_on_server_error(): void
    {
        Http::fake([
            'https://api.rajaongkir.com/*' => Http::response([
                'rajaongkir' => [
                    'status' => ['code' => 500, 'description' => 'Server error'],
                    'results' => [],
                ],
            ], 500),
        ]);

        $service = $this->makeService();

        $this->expectException(\Exception::class);

        try {
            $service->getCost('1', '2', 1000, 'jne');
        } finally {
            Http::assertSentCount(3);
        }
    }
}
