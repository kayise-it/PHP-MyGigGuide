@extends('layouts.admin')

@section('title', 'New Poll - Admin Panel')
@section('page-title', 'New Poll')

@section('content')
<div class="p-6 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-100">Create Poll</h1>
        <p class="text-slate-400">New station poll</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 p-4">
            <ul class="text-sm text-red-300 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.polls.store') }}" class="space-y-6">
        @csrf

        <div>
            <label for="context" class="block text-sm font-medium text-slate-300 mb-1">Station context</label>
            <select id="context" name="context"
                    class="input w-full" required>
                <option value="">— Select station —</option>
                <option value="vowfm"   @selected(old('context') === 'vowfm')>VOW 88.1</option>
                <option value="risefm"  @selected(old('context') === 'risefm')>RISE fm</option>
                <option value="hot1027" @selected(old('context') === 'hot1027')>HOT 102.7</option>
                <option value="fm919"   @selected(old('context') === 'fm919')>91.9 FM</option>
                <option value="mix938"  @selected(old('context') === 'mix938')>Mix 93.8</option>
            </select>
        </div>

        <div>
            <label for="question" class="block text-sm font-medium text-slate-300 mb-1">Question</label>
            <input type="text" id="question" name="question"
                   value="{{ old('question') }}"
                   placeholder="e.g. What is your favourite genre?"
                   class="input w-full" required maxlength="500">
        </div>

        <div>
            <label for="options_raw" class="block text-sm font-medium text-slate-300 mb-1">Options</label>
            <textarea id="options_raw" name="options_raw"
                      rows="5"
                      placeholder="Enter one option per line (minimum 2)"
                      class="input w-full font-mono text-sm resize-y" required>{{ old('options_raw') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">One option per line. Blank lines are ignored.</p>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" id="active" name="active" value="1"
                   class="h-4 w-4 rounded border-white/20 bg-slate-800 text-indigo-600"
                   {{ old('active', '1') ? 'checked' : '' }}>
            <label for="active" class="text-sm font-medium text-slate-300">Active (visible to users)</label>
        </div>

        <div>
            <label for="closes_at" class="block text-sm font-medium text-slate-300 mb-1">Closes at <span class="text-slate-500">(optional)</span></label>
            <input type="date" id="closes_at" name="closes_at"
                   value="{{ old('closes_at', $defaultClosesAt) }}"
                   class="input w-auto">
            <p class="mt-1 text-xs text-slate-500">Leave blank for no expiry.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Create Poll</button>
            <a href="{{ route('admin.polls.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
