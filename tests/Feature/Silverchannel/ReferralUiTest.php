<?php

declare(strict_types=1);

namespace Tests\Feature\Silverchannel;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReferralUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'SILVERCHANNEL']);
    }

    public function test_referral_buttons_have_correct_classes(): void
    {
        $referrer = User::factory()->create();
        $referrer->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.index'));

        $response->assertStatus(200);

        // Check Filter button styling (h-11, shadow, scale effect)
        // Note: We use inline classes now for immediate update
        $response->assertSee('h-11'); 
        $response->assertSee('hover:scale-105');
        
        // Check Export button styling (inline styles for color, utility classes for layout)
        $response->assertSee('background-color: #198754');
        $response->assertSee('min-w-[44px]');
        
        // Ensure btn-accent/btn-success class dependence is removed/minimized
        $response->assertDontSee('btn-accent');
    }
}
