@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Paid Features</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.feature-programs.index') }}" class="btn">Programs</a>
            <a href="{{ route('admin.paid-features.create') }}" class="btn btn-primary">Create Feature</a>
        </div>
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
                    <th class="px-4 py-2 text-left">Duration</th>
                    <th class="px-4 py-2 text-left">Price</th>
                    <th class="px-4 py-2 text-left">Active</th>
                    <th class="px-4 py-2 text-left">Packages</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($features as $feature)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $feature->name }}</td>
                        <td class="px-4 py-2 capitalize">{{ $feature->applies_to }}</td>
                        <td class="px-4 py-2">{{ $feature->duration_days }} days</td>
                        <td class="px-4 py-2">{{ $feature->currency }} {{ number_format($feature->price_cents/100, 2) }}</td>
                        <td class="px-4 py-2">{{ $feature->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2">
                            <a class="text-purple-700 hover:underline" href="{{ route('admin.feature-packages.index', $feature) }}">Manage</a>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a class="btn btn-sm" href="{{ route('admin.paid-features.edit', $feature) }}">Edit</a>
                            <form action="{{ route('admin.paid-features.destroy', $feature) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this feature?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No paid features yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $features->links() }}</div>
    </div>
</div>
@endsection


