@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-6">Create Feature Program</h1>

    <form action="{{ route('admin.feature-programs.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" name="name" class="input" required />
        </div>
        <div>
            <label class="block text-sm font-medium">Applies To</label>
            <select name="applies_to" class="input" required>
                @foreach(['artist','venue','event','organiser'] as $t)
                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" class="input" rows="3"></textarea>
        </div>
        <div class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" value="1" checked /> <span>Active</span>
        </div>
        <div class="flex items-center space-x-3">
            <button class="btn btn-primary" type="submit">Create</button>
            <a href="{{ route('admin.feature-programs.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection









