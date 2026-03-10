@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-6">Edit Feature Program</h1>

    <form action="{{ route('admin.feature-programs.update', $program) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" name="name" class="input" value="{{ old('name', $program->name) }}" required />
        </div>
        <div>
            <label class="block text-sm font-medium">Applies To</label>
            <select name="applies_to" class="input" required>
                @foreach(['artist','venue','event','organiser'] as $t)
                    <option value="{{ $t }}" @selected($program->applies_to === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" class="input" rows="3">{{ old('description', $program->description) }}</textarea>
        </div>
        <div class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" value="1" @checked($program->is_active) /> <span>Active</span>
        </div>
        <div class="flex items-center space-x-3">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.feature-programs.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection









