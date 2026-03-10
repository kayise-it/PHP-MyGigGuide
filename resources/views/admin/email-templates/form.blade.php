@extends('layouts.admin')

@php
    $isEdit = $mode === 'edit';
@endphp

@section('title', ($isEdit ? 'Edit' : 'Create') . ' Email Template - Admin Dashboard')
@section('page-title', ($isEdit ? 'Edit' : 'Create') . ' Email Template')

@push('head')
    {{-- CKEditor 5 Classic from CDN (no API key required) --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.querySelector('textarea.wysiwyg');
            const preview = document.getElementById('email-preview');

            if (textarea && window.ClassicEditor) {
                ClassicEditor
                    .create(textarea, {
                        toolbar: [
                            'undo', 'redo', '|',
                            'heading', '|',
                            'bold', 'italic', 'underline', '|',
                            'bulletedList', 'numberedList', '|',
                            'link', 'insertTable'
                        ]
                    })
                    .then(editor => {
                        const updatePreview = () => {
                            if (preview) {
                                preview.innerHTML = editor.getData();
                            }
                        };

                        // Initial render
                        updatePreview();

                        editor.model.document.on('change:data', updatePreview);
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                    });
            } else if (preview && textarea) {
                // Fallback: simple textarea → preview sync
                preview.innerHTML = textarea.value;
                textarea.addEventListener('input', function () {
                    preview.innerHTML = this.value;
                });
            }
        });
    </script>
@endpush

@section('content')
    <div class="max-w-5xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $isEdit ? 'Edit Email Template' : 'Create Email Template' }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Use the WYSIWYG editor to design HTML emails. You can use Blade-style variables like
                    <code>@{{ $user->name }}</code> or <code>@{{ $registerUrl }}</code>.
                </p>
            </div>
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
                Back to templates
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <strong>There were some problems with your input:</strong>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ $isEdit ? route('admin.email-templates.update', $template) : route('admin.email-templates.store') }}"
            method="POST"
            class="space-y-6"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="key" class="block text-sm font-medium text-gray-700">
                            Template Key
                        </label>
                        <input
                            type="text"
                            id="key"
                            name="key"
                            class="input mt-1 font-mono text-sm"
                            value="{{ old('key', $template->key) }}"
                            placeholder="e.g. claim_invitation, registration_verification">
                        <p class="mt-1 text-xs text-gray-500">
                            Used in code to look up this template. Keep it short, unique, and lowercase with underscores.
                        </p>
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Display Name
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="input mt-1"
                            value="{{ old('name', $template->name) }}"
                            placeholder="Claim Your Artist Profile"
                            required
                        >
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">
                        Email Subject (optional)
                    </label>
                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        class="input mt-1"
                        value="{{ old('subject', $template->subject) }}"
                        placeholder="🎵 Claim Your Artist Profile!"
                    >
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description (optional)
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="2"
                        class="input mt-1"
                        placeholder="Short internal note about when this template is used."
                    >{{ old('description', $template->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="body_html" class="block text-sm font-medium text-gray-700">
                            HTML Body
                        </label>
                        <textarea
                            id="body_html"
                            name="body_html"
                            class="input mt-1 wysiwyg"
                        >{{ old('body_html', $template->body_html) }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">
                            This content is sent as the full HTML email. You can use Blade variables like
                            <code>@{{ $user->name }}</code>, <code>@{{ $verificationUrl }}</code>,
                            <code>@{{ $entityName }}</code>, etc.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">
                                Live Preview
                            </label>
                            <span class="text-xs text-gray-400">Rendered as recipients will roughly see it</span>
                        </div>
                        <div
                            id="email-preview"
                            class="mt-1 border border-gray-200 rounded-lg bg-white overflow-auto max-h-[520px] p-4 text-sm"
                            style="background-image: linear-gradient(45deg, #f3f4f6 25%, transparent 25%, transparent 50%, #f3f4f6 50%, #f3f4f6 75%, transparent 75%, transparent); background-size: 16px 16px;"
                        >
                            {{-- Preview content will be injected by JS --}}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                        >
                        Active (use this template in outgoing emails)
                    </label>

                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Save Changes' : 'Create Template' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

