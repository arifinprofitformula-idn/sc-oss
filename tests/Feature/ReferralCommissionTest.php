<?php

namespace Tests\Feature;

use App\Events\SilverchannelApproved;
use App\Listeners\AwardRegistrationCommission;
use App\Models\AuditLog;
use App\Models\Package;
use App\Models\ReferralCommission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class ReferralCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup roles
        Role::create(['name' => 'SUPER_ADMIN']);
        Role::create(['name' => 'SILVERCHANNEL']);
    }

    public function test_admin_can_set_commission_on_package_creation()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'Test Package',
            'price' => 1000000,
            'weight' => 1000,
            'description' => 'Test Description',
            'benefits' => ['Benefit 1'],
            'duration_days' => 30,
            'is_active' => 1,
            'commission_type' => 'percentage',
            'commission_value' => 10, // 10%
        ]);

        $response->assertRedirect(route('admin.packages.index'));
        
        $this->assertDatabaseHas('packages', [
            'name' => 'Test Package',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
        ]);
    }

    public function test_admin_validation_for_commission_fields()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'Test Package',
            'price' => 1000000,
            'weight' => 1000,
            'description' => 'Test Description',
            'benefits' => ['Benefit 1'],
            'is_active' => 1,
            'commission_type' => 'invalid_type', // Invalid
            'commission_value' => -50, // Invalid
        ]);

        $response->assertSessionHasErrors(['commission_type', 'commission_value']);
    }

    public function test_commission_is_awarded_on_silverchannel_approval_fixed()
    {
        // 1. Setup Data
        $referrer = User::factory()->create(['referral_code' => 'REF123']);
        $applicant = User::factory()->create(['referrer_id' => $referrer->id, 'status' => 'pending']);
        
        $package = Package::factory()->create([
            'price' => 500000,
            'commission_type' => 'fixed',
            'commission_value' => 50000,
            'is_active' => true,
        ]);

        // Mock AuditLog to simulate registration with this package
        AuditLog::create([
            'user_id' => $applicant->id,
            'action' => 'REGISTER_SILVERCHANNEL_WITH_PAYMENT',
            'model_type' => User::class,
            'model_id' => $applicant->id,
            'old_values' => [],
            'new_values' => ['package_id' => $package->id],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent',
        ]);

        // 2. Trigger Event directly (simulate approval)
        $event = new SilverchannelApproved($applicant);
        $listener = app(AwardRegistrationCommission::class);
        $listener->handle($event);

        // 3. Assertions
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $applicant->id,
            'package_id' => $package->id,
            'amount' => 50000,
            'commission_type' => 'fixed',
            'status' => 'PENDING',
        ]);
    }

    public function test_commission_is_awarded_on_silverchannel_approval_percentage()
    {
        // 1. Setup Data
        $referrer = User::factory()->create(['referral_code' => 'REF123']);
        $applicant = User::factory()->create(['referrer_id' => $referrer->id, 'status' => 'pending']);
        
        $package = Package::factory()->create([
            'price' => 1000000,
            'commission_type' => 'percentage',
            'commission_value' => 10, // 10%
            'is_active' => true,
        ]);

        // Mock AuditLog
        AuditLog::create([
            'user_id' => $applicant->id,
            'action' => 'REGISTER_SILVERCHANNEL_WITH_PAYMENT',
            'model_type' => User::class,
            'model_id' => $applicant->id,
            'old_values' => [],
            'new_values' => ['package_id' => $package->id],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent',
        ]);

        // 2. Trigger Event
        $event = new SilverchannelApproved($applicant);
        $listener = app(AwardRegistrationCommission::class);
        $listener->handle($event);

        // 3. Assertions
        // 10% of 1,000,000 = 100,000
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $applicant->id,
            'amount' => 100000,
            'commission_type' => 'percentage',
        ]);
    }

    public function test_commission_duplicate_prevention()
    {
        $referrer = User::factory()->create();
        $applicant = User::factory()->create(['referrer_id' => $referrer->id]);
        $package = Package::factory()->create();

         // Mock AuditLog
         AuditLog::create([
            'user_id' => $applicant->id,
            'action' => 'REGISTER_SILVERCHANNEL_WITH_PAYMENT',
            'model_type' => User::class,
            'model_id' => $applicant->id,
            'old_values' => [],
            'new_values' => ['package_id' => $package->id],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent',
        ]);

        $event = new SilverchannelApproved($applicant);
        $listener = app(AwardRegistrationCommission::class);
        
        // First run
        $listener->handle($event);
        $this->assertDatabaseCount('referral_commissions', 1);

        // Second run
        $listener->handle($event);
        $this->assertDatabaseCount('referral_commissions', 1);
    }

    public function test_fallback_to_active_package_if_audit_log_missing()
    {
        $referrer = User::factory()->create();
        $applicant = User::factory()->create(['referrer_id' => $referrer->id]);
        
        // Create active package
        $package = Package::factory()->create([
            'price' => 200000,
            'commission_type' => 'fixed',
            'commission_value' => 20000,
            'is_active' => true,
        ]);

        // No AuditLog created

        $event = new SilverchannelApproved($applicant);
        $listener = app(AwardRegistrationCommission::class);
        $listener->handle($event);

        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $referrer->id,
            'package_id' => $package->id,
            'amount' => 20000,
        ]);
    }

    public function test_admin_can_update_commission_on_package()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $package = Package::factory()->create([
            'commission_type' => 'fixed',
            'commission_value' => 50000,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.packages.update', $package), [
            'name' => $package->name,
            'price' => $package->price,
            'weight' => $package->weight,
            'description' => $package->description,
            'benefits' => $package->benefits,
            'duration_days' => $package->duration_days,
            'is_active' => 1,
            'commission_type' => 'percentage',
            'commission_value' => 15,
        ]);

        $response->assertRedirect(route('admin.packages.index'));

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'commission_type' => 'percentage',
            'commission_value' => 15.00,
        ]);
    }
}
