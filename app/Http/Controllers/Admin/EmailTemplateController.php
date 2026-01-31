<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    protected $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    public function index(Request $request)
    {
        $query = EmailTemplate::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%");
            });
        }

        $templates = $query->latest()->paginate(10);
        return view('admin.email-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:email_templates,key',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
            'sync_brevo' => 'nullable|boolean',
        ]);

        $template = EmailTemplate::create($validated);

        if ($request->boolean('sync_brevo')) {
            $this->syncToBrevo($template);
        }

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:email_templates,key,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
            'sync_brevo' => 'nullable|boolean',
        ]);

        $emailTemplate->update($validated);

        if ($request->boolean('sync_brevo')) {
            $this->syncToBrevo($emailTemplate);
        }

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    public function duplicate(EmailTemplate $emailTemplate)
    {
        $newTemplate = $emailTemplate->replicate();
        $newTemplate->name = $newTemplate->name . ' (Copy)';
        $newTemplate->key = $newTemplate->key . '_copy_' . time();
        $newTemplate->brevo_id = null;
        $newTemplate->save();

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template duplicated successfully.');
    }

    public function export(EmailTemplate $emailTemplate)
    {
        $fileName = Str::slug($emailTemplate->name) . '.html';
        return response()->streamDownload(function () use ($emailTemplate) {
            echo $emailTemplate->body;
        }, $fileName);
    }

    public function sync(EmailTemplate $emailTemplate)
    {
        $result = $this->syncToBrevo($emailTemplate);

        if ($result['success']) {
            return back()->with('success', 'Synced with Brevo successfully. Brevo ID: ' . $emailTemplate->brevo_id);
        } else {
            return back()->with('error', 'Sync failed: ' . ($result['message'] ?? 'Unknown error'));
        }
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $content = $emailTemplate->body;
        $variables = [
            '{{name}}' => 'John Doe',
            '{{reset_url}}' => url('/reset-password/token'),
            '{{count}}' => '60',
            '{{app_name}}' => config('app.name'),
            '{{email}}' => 'john@example.com',
        ];

        foreach ($variables as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        return view('admin.email-templates.preview', compact('emailTemplate', 'content'));
    }

    protected function syncToBrevo(EmailTemplate $template)
    {
        if ($template->brevo_id) {
            $response = $this->integrationService->updateBrevoTemplate(
                $template->brevo_id,
                $template->name,
                $template->subject,
                $template->body,
                $template->is_active
            );
        } else {
            $response = $this->integrationService->createBrevoTemplate(
                $template->name,
                $template->subject,
                $template->body,
                $template->is_active
            );

            if (isset($response['id'])) {
                $template->brevo_id = $response['id'];
                $template->save();
            }
        }

        if (isset($response['id']) || (isset($response['success']) && $response['success'])) {
             return ['success' => true];
        }

        return ['success' => false, 'message' => json_encode($response)];
    }
}
