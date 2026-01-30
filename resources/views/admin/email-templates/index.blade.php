@extends('layouts.admin')

@section('title', 'Email Templates - Admin Dashboard')
@section('page-title', 'Email Templates')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Email Templates</h2>
            <p class="mt-1 text-sm text-gray-600">
                Manage reusable HTML email templates for registration, verification, claims, and password flows.
            </p>
        </div>
        <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary">
            + New Template
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Key
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Subject
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-4 py-3 text-sm font-mono text-gray-800">
                            {{ $template->key }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $template->name }}
                            @if ($template->description)
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ Str::limit($template->description, 80) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $template->subject ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($template->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-right space-x-2">
                            <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn btn-sm btn-secondary">
                                Edit
                            </a>
                            <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Delete this template? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                            No email templates yet. Click “New Template” to create your first one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

