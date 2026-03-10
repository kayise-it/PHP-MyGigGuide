@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Feature Programs</h1>
        <a href="{{ route('admin.feature-programs.create') }}" class="btn btn-primary">Create Program</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Applies To</th>
                    <th class="px-4 py-2 text-left">Active</th>
                    <th class="px-4 py-2 text-left">Feature Types</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $program->name }}</td>
                        <td class="px-4 py-2 capitalize">{{ $program->applies_to }}</td>
                        <td class="px-4 py-2">{{ $program->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2">{{ $program->features_count }}</td>
                        <td class="px-4 py-2 text-right">
                            <a class="btn btn-sm" href="{{ route('admin.feature-programs.edit', $program) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No programs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $programs->links() }}</div>
    </div>
</div>
@endsection









