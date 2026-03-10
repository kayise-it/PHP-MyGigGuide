@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-6">Edit Package — {{ $paidFeature->name }}</h1>
    <form action="{{ route('admin.feature-packages.update', [$paidFeature, $featurePackage]) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" name="name" class="input" value="{{ old('name', $featurePackage->name) }}" required />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">Duration (days)</label>
                <input type="number" name="duration_days" class="input" min="1" value="{{ old('duration_days', $featurePackage->duration_days) }}" required />
            </div>
            <div>
                <label class="block text-sm font-medium">Price (cents)</label>
                <input type="number" name="price_cents" class="input" min="0" step="1" value="{{ old('price_cents', $featurePackage->price_cents) }}" required />
            </div>
            <div>
                <label class="block text-sm font-medium">Currency</label>
                <input type="text" name="currency" class="input" value="{{ old('currency', $featurePackage->currency) }}" maxlength="3" required />
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" value="1" @checked($featurePackage->is_active) />
            <span>Active</span>
        </div>
        <div class="flex items-center space-x-3">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.feature-packages.index', $paidFeature) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection









