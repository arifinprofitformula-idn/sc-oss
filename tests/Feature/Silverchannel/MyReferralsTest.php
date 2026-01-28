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

        $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.export'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_can_filter_referrals_by_status(): void
    {
        $referrer = $this->createActiveSilverchannelUser();

        $userPending = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Pending User']);
        $userConverted = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Converted User']);

        // Set status via ReferralFollowUp
        ReferralFollowUp::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $userConverted->id,
            'status' => 'CONVERTED'
        ]);

        $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.index', ['status' => 'CONVERTED']))
            ->assertOk()
            ->assertSee('Converted User')
            ->assertDontSee('Pending User');
    }

    public function test_can_filter_referrals_by_city(): void
    {
        $referrer = $this->createActiveSilverchannelUser();

        $userJakarta = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Jakarta User', 'city_name' => 'Jakarta']);
        $userBandung = User::factory()->create(['referrer_id' => $referrer->id, 'name' => 'Bandung User', 'city_name' => 'Bandung']);

        $this->actingAs($referrer)
            ->get(route('silverchannel.referrals.index', ['city' => 'Jakarta']))
            ->assertOk()
            ->assertSee('Jakarta User')
            ->assertDontSee('Bandung User');
    }

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
