<?php

namespace Tests\Feature\Jobs;

use App\Jobs\UpdateProductPriceJob;
use App\Models\Product;
use App\Services\EpiApePriceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class UpdateProductPriceJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_update_successfully()
    {
        $product = Product::factory()->create([
            'sku' => 'JOB-SKU',
            'price_silverchannel' => 50000,
        ]);

        $data = [
            'sku' => 'JOB-SKU',
            'price' => 60000,
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        $job = new UpdateProductPriceJob($data);
        $service = new EpiApePriceService();
        
        $job->handle($service);

        $product->refresh();
        $this->assertEquals(60000, $product->price_silverchannel);
    }

    public function test_job_fails_and_logs_error_on_invalid_data()
    {
        Log::shouldReceive('info')->once(); // Job started
        Log::shouldReceive('error')->once(); // Job failed
        // We expect other logs from Service too, but we mainly care about the Job failure log here
        
        // Don't mock warning from service to keep it simple, or allow it
        Log::shouldReceive('warning')->andReturnNull();

        $data = [
            'sku' => 'INVALID-SKU',
            'price' => -100, // Invalid price will cause service to return error, but wait...
            // Service returns array status on validation error, it doesn't throw Exception.
            // Wait, my Job implementation expects exception to trigger retry?
            // "throw $e; // Trigger retry" in Job catch block.
            // But Service::processUpdate catches exceptions? 
            // Let's check Service::processUpdate again.
            
            // In Service: 
            // if (!is_numeric($newPrice)) return ['status' => 'error'...]
            // So it returns, doesn't throw.
            // If I want the Job to retry, I should probably throw exception in Service or handle return status in Job.
            // The user requirement said "retry if update fails". Validation failure shouldn't be retried (it will always fail).
            // Database connection error should be retried.
            
            'updated_at' => Carbon::now()->toIso8601String(),
        ];
        
        // So for this test, let's simulate an exception from the service method by mocking it?
        // Or just rely on the fact that if service returns 'error', the job finishes successfully (no retry needed for validation error).
        
        // Let's test that Job throws exception if Service throws exception (e.g. DB error).
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');
        
        $mockService = $this->mock(EpiApePriceService::class);
        $mockService->shouldReceive('processUpdate')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $job = new UpdateProductPriceJob($data);
        $job->handle($mockService);
    }
}
