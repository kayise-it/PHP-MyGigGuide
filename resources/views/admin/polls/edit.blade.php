@extends('layouts.admin')

@section('title', 'Edit Poll - Admin Panel')
@section('page-title', 'Edit Poll')

@section('content')
<div class="p-6 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-100">Edit Poll</h1>
        <p class="text-slate-400">ID #{{ $poll->id }}</p>
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

    @if($totalVotes > 0)
        <div class="mb-6 rounded-lg border border-amber-500/30 bg-amber-500/10 p-4 flex gap-3">
            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-amber-200">
                This poll has <strong>{{ $totalVotes }} {{ Str::plural('vote', $totalVotes) }}</strong>.
                Editing an active poll with votes may confuse voters — consider closing it first and creating a new one.
            </div>
        </div>
    @endif

    {{-- Results summary --}}
    <div class="mb-6 rounded-lg border border-white/10 bg-slate-900/70 p-5">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wide">
                Current results ({{ $totalVotes }} {{ Str::plural('vote', $totalVotes) }})
            </h3>
            <a href="{{ route('admin.polls.show', $poll) }}" class="text-indigo-300 hover:text-indigo-200 text-sm font-medium">
                Full results →
            </a>
        </div>
        @include('admin.polls._results', [
            'poll' => $poll,
            'totalVotes' => $totalVotes,
        ])
    </div>

    <form method="POST" action="{{ route('admin.polls.update', $poll) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="context" class="block text-sm font-medium text-slate-300 mb-1">Station context</label>
            <select id="context" name="context" class="input w-full" required>
                <option value="">— Select station —</option>
                <option value="vowfm"   @selected(old('context', $poll->context) === 'vowfm')>VOW 88.1</option>
                <option value="risefm"  @selected(old('context', $poll->context) === 'risefm')>RISE fm</option>
                <option value="hot1027" @selected(old('context', $poll->context) === 'hot1027')>HOT 102.7</option>
                <option value="fm919"   @selected(old('context', $poll->context) === 'fm919')>91.9 FM</option>
                <option value="mix938"  @selected(old('context', $poll->context) === 'mix938')>Mix 93.8</option>
            </select>
        </div>

        <div>
            <label for="question" class="block text-sm font-medium text-slate-300 mb-1">Question</label>
            <input type="text" id="question" name="question"
                   value="{{ old('question', $poll->question) }}"
                   class="input w-full" required maxlength="500">
        </div>

        <div>
            <label for="options_raw" class="block text-sm font-medium text-slate-300 mb-1">Options</label>
            <textarea id="options_raw" name="options_raw"
                      rows="5"
                      placeholder="Enter one option per line (minimum 2)"
                      class="input w-full font-mono text-sm resize-y" required>{{ old('options_raw', $optionsText) }}</textarea>
            <p class="mt-1 text-xs text-slate-500">One option per line. Blank lines are ignored.</p>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" id="active" name="active" value="1"
                   class="h-4 w-4 rounded border-white/20 bg-slate-800 text-indigo-600"
                   {{ old('active', $poll->active) ? 'checked' : '' }}>
            <label for="active" class="text-sm font-medium text-slate-300">Active (visible to users)</label>
        </div>

        <div>
            <label for="closes_at" class="block text-sm font-medium text-slate-300 mb-1">Closes at <span class="text-slate-500">(optional)</span></label>
            <input type="date" id="closes_at" name="closes_at"
                   value="{{ old('closes_at', $poll->closes_at?->format('Y-m-d')) }}"
                   class="input w-auto">
            <p class="mt-1 text-xs text-slate-500">Leave blank for no expiry.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.polls.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
