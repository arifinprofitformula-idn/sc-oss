<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_active_package_can_exist()
    {
        // Create first active package
        $package1 = Package::create([
            'name' => 'Package 1',
            'price' => 100000,
            'is_active' => true,
            'weight' => 1000,
        ]);

        $this->assertTrue($package1->fresh()->is_active);

        // Create second active package
        $package2 = Package::create([
            'name' => 'Package 2',
            'price' => 200000,
            'is_active' => true,
            'weight' => 1000,
        ]);

        // Assert package 2 is active, package 1 is now inactive
        $this->assertTrue($package2->fresh()->is_active);
        $this->assertFalse($package1->fresh()->is_active);
    }

    public function test_updating_package_to_active_deactivates_others()
    {
        $package1 = Package::create([
            'name' => 'Package 1',
            'price' => 100000,
            'is_active' => true,
            'weight' => 1000,
        ]);

        $package2 = Package::create([
            'name' => 'Package 2',
            'price' => 200000,
            'is_active' => false,
            'weight' => 1000,
        ]);

        $this->assertTrue($package1->fresh()->is_active);
        $this->assertFalse($package2->fresh()->is_active);

        // Update package 2 to active
        $package2->update(['is_active' => true]);

        $this->assertFalse($package1->fresh()->is_active);
        $this->assertTrue($package2->fresh()->is_active);
    }
}
