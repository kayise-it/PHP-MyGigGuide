@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto py-6">
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-semibold">Packages for: {{ $paidFeature->name }}</h1>
        <a href="{{ route('admin.feature-packages.create', $paidFeature) }}" class="btn btn-primary">Add Package</a>
    </div>
    <p class="text-gray-600 mb-4">Feature Type: {{ ucfirst($paidFeature->applies_to) }} • Program: {{ $paidFeature->program->name ?? '—' }}</p>
    <div class="bg-white shadow rounded">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Duration</th>
                    <th class="px-4 py-2 text-left">Price</th>
                    <th class="px-4 py-2 text-left">Active</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $pkg)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $pkg->name }}</td>
                        <td class="px-4 py-2">{{ $pkg->duration_days }} days</td>
                        <td class="px-4 py-2">{{ $pkg->currency }} {{ number_format($pkg->price_cents/100, 2) }}</td>
                        <td class="px-4 py-2">{{ $pkg->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right">
                            <a class="btn btn-sm" href="{{ route('admin.feature-packages.edit', [$paidFeature, $pkg]) }}">Edit</a>
                            <form action="{{ route('admin.feature-packages.destroy', [$paidFeature, $pkg]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this package?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No packages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection









