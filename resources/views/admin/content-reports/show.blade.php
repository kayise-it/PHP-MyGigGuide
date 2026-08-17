@extends('layouts.admin')

@section('title', 'Content Report #'.$report->id.' - Admin Panel')

@section('content')
<div class="p-6 max-w-4xl">
    <a href="{{ route('admin.content-reports.index') }}" class="text-indigo-300 hover:text-indigo-200 mb-4 inline-block">&larr; Back to reports</a>

    @if(session('success'))
        <div class="mb-4 alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col gap-2 mb-6 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Report #{{ $report->id }}</h1>
            <p class="text-slate-400">Submitted {{ $report->created_at?->format('M j, Y g:i A') }}</p>
        </div>
        @if($report->status === 'new')
            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-amber-500/20 text-amber-200 border border-amber-500/30 w-fit">New</span>
        @elseif($report->status === 'resolved')
            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-500/20 text-green-200 border border-green-500/30 w-fit">Resolved</span>
        @else
            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-slate-700 text-slate-200 border border-white/10 w-fit">Dismissed</span>
        @endif
    </div>

    <div class="rounded-lg border border-white/10 bg-slate-900/70 p-6 space-y-5 mb-6">
        <div>
            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">Listing</h2>
            <p class="text-lg font-medium text-slate-100 mt-1">{{ $report->reportableTitle() }}</p>
            <p class="text-sm text-slate-400">{{ $report->reportableTypeLabel() }} #{{ $report->reportable_id }}</p>
            @if($editRoute)
                <a href="{{ $editRoute }}" class="inline-block mt-2 text-indigo-300 hover:text-indigo-200 text-sm font-medium">Edit in admin &rarr;</a>
            @endif
        </div>

        <div>
            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">Category</h2>
            <p class="text-slate-100 mt-1">{{ $report->categoryLabel() }}</p>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">Reporter</h2>
            <p class="text-slate-100 mt-1">{{ $report->user?->name ?? 'Unknown' }}</p>
            @if($report->user?->email)
                <p class="text-sm text-slate-400">{{ $report->user->email }}</p>
            @endif
        </div>

        @if($report->message)
            <div>
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">Note</h2>
                <p class="text-slate-100 mt-1 whitespace-pre-wrap">{{ $report->message }}</p>
            </div>
        @endif

        @if($report->admin_notes)
            <div>
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">Admin notes</h2>
                <p class="text-slate-100 mt-1 whitespace-pre-wrap">{{ $report->admin_notes }}</p>
            </div>
        @endif

        @if($report->resolved_at)
            <div>
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide">Closed</h2>
                <p class="text-slate-100 mt-1">{{ $report->resolved_at->format('M j, Y g:i A') }}</p>
                @if($report->resolvedBy)
                    <p class="text-sm text-slate-400">by {{ $report->resolvedBy->name }}</p>
                @endif
            </div>
        @endif
    </div>

    @if($report->status === 'new')
        <div class="grid gap-6 md:grid-cols-2">
            <form method="POST" action="{{ route('admin.content-reports.resolve', $report) }}" class="rounded-lg border border-white/10 bg-slate-900/70 p-6">
                @csrf
                <h3 class="font-semibold text-slate-100 mb-3">Mark resolved</h3>
                <p class="text-sm text-slate-400 mb-4">Use when you fixed the listing or confirmed the issue.</p>
                <textarea name="admin_notes" rows="3" placeholder="Optional internal note…"
                          class="input mb-4">{{ old('admin_notes') }}</textarea>
                <button type="submit" class="btn btn-primary" style="background:#16a34a;border-color:#16a34a">Resolve</button>
            </form>

            <form method="POST" action="{{ route('admin.content-reports.dismiss', $report) }}" class="rounded-lg border border-white/10 bg-slate-900/70 p-6">
                @csrf
                <h3 class="font-semibold text-slate-100 mb-3">Dismiss</h3>
                <p class="text-sm text-slate-400 mb-4">Use for duplicates, bad faith, or not actionable.</p>
                <textarea name="admin_notes" rows="3" placeholder="Optional internal note…"
                          class="input mb-4">{{ old('admin_notes') }}</textarea>
                <button type="submit" class="btn btn-secondary">Dismiss</button>
            </form>
        </div>
    @endif
</div>
@endsection
