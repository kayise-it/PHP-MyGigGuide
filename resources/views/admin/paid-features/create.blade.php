@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-6">Create Paid Feature</h1>

    <form action="{{ route('admin.paid-features.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" name="name" class="input" value="{{ old('name') }}" required />
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Program</label>
            <select name="feature_program_id" class="input">
                <option value="">— None —</option>
                @foreach(\App\Models\FeatureProgram::orderBy('name')->get() as $program)
                    <option value="{{ $program->id }}">{{ $program->name }} ({{ ucfirst($program->applies_to) }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Applies To</label>
            <select name="applies_to" class="input" required>
                <option value="artist">Artist</option>
                <option value="venue">Venue</option>
                <option value="event">Event</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" class="input" rows="4">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">Duration (days)</label>
                <input type="number" name="duration_days" class="input" min="1" value="{{ old('duration_days', 7) }}" required />
            </div>
            <div>
                <label class="block text-sm font-medium">Price (ZAR)</label>
                <input type="number" name="price_cents" class="input" min="0" step="1" value="{{ old('price_cents', 0) }}" required />
                <p class="text-xs text-gray-500">Enter cents (e.g. 9999 = R99.99)</p>
            </div>
            <div>
                <label class="block text-sm font-medium">Currency</label>
                <input type="text" name="currency" class="input" value="{{ old('currency', 'ZAR') }}" maxlength="3" required />
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" value="1" checked />
            <span>Active</span>
        </div>
        <div>
            <label class="block text-sm font-medium">Settings (JSON)</label>
            <textarea name="settings" class="input" rows="3" placeholder='{"weight": 10, "badge":"Featured"}'>{{ old('settings') }}</textarea>
        </div>

        <div class="flex items-center space-x-3">
            <button class="btn btn-primary" type="submit">Create</button>
            <a href="{{ route('admin.paid-features.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection


