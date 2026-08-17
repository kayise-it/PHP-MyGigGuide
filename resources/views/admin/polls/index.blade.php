@extends('layouts.admin')

@section('title', 'Polls - Admin Panel')
@section('description', 'Manage station polls for the mobile app.')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Polls</h1>
            <p class="text-gray-600">Station polls shown in the app (Mix 93.8, VOW FM, etc.)</p>
        </div>
        <a href="{{ route('admin.polls.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200">
            Create Poll
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search question..." class="px-3 py-2 border border-gray-300 rounded-lg">
            <select name="context" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All contexts</option>
                @foreach($contexts as $key => $label)
                <option value="{{ $key }}" @selected(request('context') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Context</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Votes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($polls as $poll)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $contexts[$poll->context] ?? $poll->context }}</td>
                    <td class="px-6 py-4 text-sm">{{ $poll->question }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $poll->votes()->count() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($poll->isOpen())
                        <span class="text-green-700">Open</span>
                        @else
                        <span class="text-gray-500">Closed</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('admin.polls.edit', $poll) }}" class="text-purple-600 hover:text-purple-800">Edit</a>
                        @if($poll->isOpen())
                        <form action="{{ route('admin.polls.close', $poll) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-amber-600 hover:text-amber-800">Close</button>
                        </form>
                        @endif
                        <form action="{{ route('admin.polls.destroy', $poll) }}" method="POST" class="inline" onsubmit="return confirm('Delete this poll?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No polls yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $polls->links() }}</div>
</div>
@endsection
