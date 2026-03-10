@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Confirm Boost Purchase</h1>
    <div class="bg-white shadow rounded p-6 space-y-3">
        <p><strong>Feature:</strong> {{ $feature->name }} ({{ ucfirst($feature->applies_to) }})</p>
        <p><strong>Duration:</strong> {{ $feature->duration_days }} days</p>
        <p><strong>Price:</strong> {{ $feature->currency }} {{ number_format($feature->price_cents/100, 2) }}</p>
        <form action="{{ route('features.purchase') }}" method="POST" class="mt-4">
            @csrf
            <input type="hidden" name="feature_id" value="{{ $feature->id }}">
            <input type="hidden" name="featureable_id" value="{{ $featureableId }}">
            <input type="hidden" name="featureable_type" value="{{ $featureableType }}">
            <button class="btn btn-primary" type="submit">Pay and Activate</button>
        </form>
    </div>
</div>
@endsection









