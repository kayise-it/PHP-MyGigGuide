<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('key')->get();

        return view('admin.email-templates.index', compact('templates'));
    }

    public function create()
    {
        $template = new EmailTemplate([
            'is_active' => true,
        ]);

        return view('admin.email-templates.form', [
            'template' => $template,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request, isUpdate: false);

        EmailTemplate::create($data);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.form', [
            'template' => $emailTemplate,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $this->validatedData($request, isUpdate: true, template: $emailTemplate);

        $emailTemplate->update($data);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template deleted.');
    }

    protected function validatedData(Request $request, bool $isUpdate, ?EmailTemplate $template = null): array
    {
        $keyRule = $isUpdate
            ? 'required|string|max:191|unique:email_templates,key,' . ($template?->id ?? 'NULL') . ',id'
            : 'required|string|max:191|unique:email_templates,key';

        return $request->validate([
            'key' => $keyRule,
            'name' => 'required|string|max:191',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'body_html' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

