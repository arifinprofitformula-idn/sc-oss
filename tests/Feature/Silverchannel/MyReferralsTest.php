<?php

declare(strict_types=1);

namespace Tests\Feature\Silverchannel;

use App\Models\ReferralFollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MyReferralsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'SILVERCHANNEL']);
    }

    private function createActiveSilverchannelUser(): User
    {
        $user = User::factory()->create([
            'status' => 'ACTIVE',
            'phone' => '08123456789',
            'nik' => '1234567890123456',
            'address' => 'Jalan Test',
            'province_id' => '1',
            'city_id' => '1',
            'subdistrict_id' => '1',
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'Single',
            'job' => 'Developer',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Test User',
        ]);
        $user->assignRole('SILVERCHANNEL');
        return $user;
    }

    public function test_silverchannel_can_view_my_referrals_page(): void
    {
        $referrer = $this->createActiveSilverchannelUser();

        $referred = User::factory()->create([
            'referrer_id' => $referrer->id,
        ]);

        $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.index'))
            ->assertStatus(200)
            ->assertSee('My Referrals')
            ->assertSee($referred->name);
    }

    public function test_non_silverchannel_cannot_access_my_referrals(): void
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);
        // Do not assign SILVERCHANNEL role

        $this->actingAs($user)
            ->get(route('silverchannel.referrals.index'))
            ->assertStatus(403);
    }

    public function test_update_follow_up_creates_record(): void
    {
        $referrer = $this->createActiveSilverchannelUser();

        $referred = User::factory()->create([
            'referrer_id' => $referrer->id,
        ]);

        $payload = [
            'status' => 'FOLLOW_UP',
            'last_follow_up_at' => now()->format('Y-m-d\TH:i'),
            'next_follow_up_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'note' => 'Contacted via WhatsApp',
        ];

        $this->actingAs($referrer)
            ->post(route('silverchannel.referrals.follow-up', ['referredUser' => $referred->id]), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('referral_follow_ups', [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'status' => 'FOLLOW_UP',
        ]);
    }

    public function test_export_csv_contains_headers_and_filename(): void
    {
        $referrer = $this->createActiveSilverchannelUser();

        $response = $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.export'));

        $response->assertStatus(200);
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="my-referrals-', $response->headers->get('Content-Disposition'));
        $response->assertSee('Nama Lengkap');
    }

    public function test_can_filter_referrals_by_status(): void
    {
        $referrer = $this->createActiveSilverchannelUser();

        $userActive = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Active User', 'status' => 'ACTIVE']);
        $userRejected = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Rejected User', 'status' => 'REJECTED']);

        $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.index', ['status' => 'ACTIVE']))
            ->assertOk()
            ->assertSee('Active User')
            ->assertDontSee('Rejected User');
    }

    // City filter removed per latest requirements; replaced by status-based filtering on user model.

    public function test_can_sort_referrals_by_name(): void
    {
        $referrer = $this->createActiveSilverchannelUser();

        $userA = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Aardvark']);
        $userZ = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Zebra']);

        $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.index', ['sort' => 'name', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['Aardvark', 'Zebra']);

        $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.index', ['sort' => 'name', 'direction' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['Zebra', 'Aardvark']);
    }
}
