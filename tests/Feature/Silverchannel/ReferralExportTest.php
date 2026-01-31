<?php

declare(strict_types=1);

namespace Tests\Feature\Silverchannel;

use App\Models\ReferralFollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReferralExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'SILVERCHANNEL']);
    }

    public function test_export_shows_correct_follow_up_status(): void
    {
        $referrer = User::factory()->create();
        $referrer->assignRole('SILVERCHANNEL');

        $prospect = User::factory()->create([
            'referrer_id' => $referrer->id,
            'name' => 'Prospect John',
            'email' => 'john@example.com',
        ]);

        // Create a follow-up record (Referrer follows up on Prospect)
        ReferralFollowUp::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $prospect->id,
            'status' => 'INTERESTED',
            'last_follow_up_at' => now(),
            'note' => 'Called him',
        ]);

        // Create a distraction: Prospect follows up on someone else (should NOT be picked up)
        $prospectDownline = User::factory()->create(['referrer_id' => $prospect->id]);
        ReferralFollowUp::create([
            'referrer_id' => $prospect->id,
            'referred_user_id' => $prospectDownline->id,
            'status' => 'WRONG_STATUS',
        ]);

        $response = $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.export'));

        $response->assertStatus(200);

        // stream content
        $content = $response->streamedContent();
        
        // Assert we see the correct status 'INTERESTED'
        $this->assertStringContainsString('INTERESTED', $content);
        
        // Assert we DO NOT see the wrong status (from prospect's own follow ups)
        $this->assertStringNotContainsString('WRONG_STATUS', $content);
    }
}
