@extends('layouts.admin')

@section('title', 'Content Reports - Admin Panel')
@section('description', 'Review listing errata reports from app users')

@section('content')
<div class="p-6">
    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Content reports</h1>
            <p class="text-slate-400">Errata flags on events, artists, and venues</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.content-reports.index', ['status' => 'new']) }}"
               class="px-4 py-2 rounded-lg border {{ request('status') === 'new' ? 'bg-amber-600 text-white border-amber-500' : 'bg-slate-800 text-slate-100 border-white/10 hover:bg-slate-700' }}">
                New ({{ $newCount }})
            </a>
            <a href="{{ route('admin.content-reports.index', ['status' => 'resolved']) }}"
               class="px-4 py-2 rounded-lg border {{ request('status') === 'resolved' ? 'bg-green-600 text-white border-green-500' : 'bg-slate-800 text-slate-100 border-white/10 hover:bg-slate-700' }}">
                Resolved
            </a>
            <a href="{{ route('admin.content-reports.index', ['status' => 'dismissed']) }}"
               class="px-4 py-2 rounded-lg border {{ request('status') === 'dismissed' ? 'bg-slate-600 text-white border-slate-500' : 'bg-slate-800 text-slate-100 border-white/10 hover:bg-slate-700' }}">
                Dismissed
            </a>
            <a href="{{ route('admin.content-reports.index') }}"
               class="px-4 py-2 rounded-lg border {{ !request('status') ? 'bg-indigo-600 text-white border-indigo-500' : 'bg-slate-800 text-slate-100 border-white/10 hover:bg-slate-700' }}">
                All
            </a>
        </div>
    </div>

    <form method="GET" class="mb-4">
        <div class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search message or reporter…"
                   class="input flex-1 min-w-[200px]" />
            <select name="type" class="input w-auto min-w-[140px]">
                <option value="">All types</option>
                <option value="events" @selected(request('type') === 'events')>Events</option>
                <option value="artists" @selected(request('type') === 'artists')>Artists</option>
                <option value="venues" @selected(request('type') === 'venues')>Venues</option>
            </select>
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}" />
            @endif
            <button type="submit" class="btn btn-primary">Search</button>
            @if(request()->anyFilled(['search', 'status', 'type']))
                <a href="{{ route('admin.content-reports.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="rounded-lg border border-white/10 bg-slate-900/70 overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-slate-800/90">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">When</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Listing</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Reporter</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400 uppercase"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($reports as $report)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 text-sm text-slate-300 whitespace-nowrap">
                            {{ $report->created_at?->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-slate-100">{{ $report->reportableTitle() }}</div>
                            <div class="text-slate-400">{{ $report->reportableTypeLabel() }} #{{ $report->reportable_id }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-200">{{ $report->categoryLabel() }}</td>
                        <td class="px-4 py-3 text-sm text-slate-200">
                            {{ $report->user?->name ?? '—' }}
                            @if($report->user?->email)
                                <div class="text-xs text-slate-400">{{ $report->user->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($report->status === 'new')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-500/20 text-amber-200 border border-amber-500/30">New</span>
                            @elseif($report->status === 'resolved')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-200 border border-green-500/30">Resolved</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-700 text-slate-200 border border-white/10">Dismissed</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <a href="{{ route('admin.content-reports.show', $report) }}" class="text-indigo-300 hover:text-indigo-200 font-medium">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">No reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-slate-300">{{ $reports->links() }}</div>
</div>
@endsection
