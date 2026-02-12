<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailTemplateController extends Controller
{
    /**
     * Get all email templates.
     */
    public function index()
    {
        return response()->json(EmailTemplate::all());
    }

    /**
     * Get a specific email template.
     */
    public function show($id)
    {
        $template = EmailTemplate::with(['histories' => function($query) {
            $query->with('user')->limit(10);
        }])->findOrFail($id);

        return response()->json($template);
    }

    /**
     * Update an email template.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template = EmailTemplate::findOrFail($id);

        // Save history before updating
        EmailTemplateHistory::create([
            'email_template_id' => $template->id,
            'user_id' => Auth::id(),
            'subject' => $template->subject,
            'body' => $template->body,
        ]);

        // Update template
        $template->update([
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully',
            'template' => $template
        ]);
    }

    /**
     * Revert to a previous version.
     */
    public function revert($historyId)
    {
        $history = EmailTemplateHistory::findOrFail($historyId);
        $template = EmailTemplate::findOrFail($history->email_template_id);

        // Save current state to history before reverting? 
        // Yes, always good to have a way back.
        EmailTemplateHistory::create([
            'email_template_id' => $template->id,
            'user_id' => Auth::id(),
            'subject' => $template->subject,
            'body' => $template->body,
        ]);

        $template->update([
            'subject' => $history->subject,
            'body' => $history->body,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template reverted successfully',
            'template' => $template
        ]);
    }

    /**
     * Preview template (simple rendering).
     */
    public function preview(Request $request)
    {
        $content = $request->input('body');
        // Simple variable replacement for preview
        // In real usage, this should be more robust or use the same logic as the mailer
        $variables = $request->input('variables', []);
        
        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        return response()->json(['html' => $content]);
    }
}
