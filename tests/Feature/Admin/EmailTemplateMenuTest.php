<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\EmailTemplate;
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

    public function test_email_template_section_visible_on_brevo_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        // Create a dummy template
        $template = EmailTemplate::create([
            'name' => 'Test Template',
            'key' => 'test_template',
            'subject' => 'Test Subject',
            'body' => '<p>Test Body</p>',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.integrations.email'));

        $response->assertStatus(200);
        $response->assertSee('Email Templates'); // Section header
        $response->assertSee('Test Template'); // Template name in table
        $response->assertSee('test_template'); // Template key in table
        $response->assertSee(route('admin.email-templates.create')); // Add New button
    }

    public function test_index_redirects_to_brevo()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)
            ->get(route('admin.email-templates.index'));

        $response->assertRedirect(route('admin.integrations.email'));
    }

    public function test_create_email_template()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)
            ->post(route('admin.email-templates.store'), [
                'name' => 'New Template',
                'key' => 'new_template',
                'subject' => 'New Subject',
                'body' => '<p>New Body</p>',
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.integrations.email'));
        $this->assertDatabaseHas('email_templates', [
            'name' => 'New Template',
            'key' => 'new_template',
        ]);
    }

    public function test_update_email_template()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $template = EmailTemplate::create([
            'name' => 'Old Name',
            'key' => 'old_key',
            'subject' => 'Old Subject',
            'body' => 'Old Body',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.email-templates.update', $template), [
                'name' => 'Updated Name',
                'key' => 'old_key', // Keep key same
                'subject' => 'Updated Subject',
                'body' => 'Updated Body',
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.integrations.email'));
        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_delete_email_template()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $template = EmailTemplate::create([
            'name' => 'To Delete',
            'key' => 'to_delete',
            'subject' => 'Subject',
            'body' => 'Body',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.email-templates.destroy', $template));

        $response->assertRedirect(route('admin.integrations.email'));
        $this->assertDatabaseMissing('email_templates', [
            'id' => $template->id,
        ]);
    }
}
