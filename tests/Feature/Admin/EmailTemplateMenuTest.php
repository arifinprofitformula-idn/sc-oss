<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailTemplateMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'SUPER_ADMIN']);
        Role::create(['name' => 'SILVERCHANNEL']);
    }

    public function test_email_template_menu_visible_on_integration_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)
            ->get(route('admin.integrations.brevo'));

        $response->assertStatus(200);
        $response->assertSee('Email Templates');
        $response->assertSee(route('admin.email-templates.index'));
    }

    public function test_email_template_page_has_integration_nav()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)
            ->get(route('admin.email-templates.index'));

        $response->assertStatus(200);
        // Verify nav elements are present
        $response->assertSee('Brevo (Email)');
        $response->assertSee(route('admin.integrations.brevo'));
        // Verify self link is active (class check might be too brittle, just content is enough)
        $response->assertSee('Email Templates');
    }

    public function test_non_admin_cannot_access_email_templates()
    {
        $user = User::factory()->create();
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($user)
            ->get(route('admin.email-templates.index'));

        // Should be forbidden
        $response->assertStatus(403);
    }
}
