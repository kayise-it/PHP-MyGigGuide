@extends('layouts.admin')

@section('title', 'Duplicate Names Management - My Gig Guide')
@section('page-title', 'Duplicate Names Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Duplicate Names</h2>
            <p class="mt-1 text-sm text-gray-600">Find and resolve duplicate names across all entity types</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($summary as $type => $data)
        <a href="{{ route('admin.duplicates.index', ['type' => $type]) }}" 
           class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow {{ $entityType === $type ? 'ring-2 ring-purple-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ $data['label'] }}s</p>
                    <p class="text-2xl font-bold {{ $data['duplicate_groups'] > 0 ? 'text-amber-600' : 'text-green-600' }}">
                        {{ $data['duplicate_groups'] }}
                    </p>
                    <p class="text-xs text-gray-500">duplicate groups</p>
                </div>
                <div class="p-3 rounded-full {{ $data['duplicate_groups'] > 0 ? 'bg-amber-100' : 'bg-green-100' }}">
                    @if($type === 'users')
                    <svg class="w-6 h-6 {{ $data['duplicate_groups'] > 0 ? 'text-amber-600' : 'text-green-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    @elseif($type === 'artists')
                    <svg class="w-6 h-6 {{ $data['duplicate_groups'] > 0 ? 'text-amber-600' : 'text-green-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    @elseif($type === 'organisers')
                    <svg class="w-6 h-6 {{ $data['duplicate_groups'] > 0 ? 'text-amber-600' : 'text-green-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    @elseif($type === 'venues')
                    <svg class="w-6 h-6 {{ $data['duplicate_groups'] > 0 ? 'text-amber-600' : 'text-green-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    @endif
                </div>
            </div>
            @if($data['total_records_affected'] > 0)
            <p class="mt-2 text-xs text-gray-500">{{ $data['total_records_affected'] }} records affected</p>
            @endif
        </a>
        @endforeach
    </div>

    <!-- Filter -->
    @if($entityType)
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600">Showing:</span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
            {{ $entityTypes[$entityType]['value'] ?? ucfirst($entityType) }}s
        </span>
        <a href="{{ route('admin.duplicates.index') }}" class="text-sm text-purple-600 hover:text-purple-800 ml-2">
            Show all
        </a>
    </div>
    @endif

    <!-- Duplicate Groups -->
    @forelse($duplicates as $type => $data)
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ $data['label'] }}s with Duplicate Names
                <span class="ml-2 text-sm font-normal text-gray-500">({{ count($data['groups']) }} groups)</span>
            </h3>
        </div>

        @if(empty($data['groups']))
        <div class="px-6 py-12 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No duplicates found</h3>
            <p class="mt-1 text-sm text-gray-500">All {{ strtolower($data['label']) }} names are unique.</p>
        </div>
        @else
        <div class="divide-y divide-gray-200">
            @foreach($data['groups'] as $group)
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">
                            Normalized name: <code class="px-2 py-1 bg-gray-100 rounded text-purple-600">{{ $group['normalized_name'] }}</code>
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">{{ count($group['items']) }} records share this normalized name</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Display Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Identifier</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($group['items'] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">#{{ $item['id'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $item['name'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $item['identifier'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $item['created_at'] ? \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end space-x-3">
                                        @if($type === 'users')
                                        <a href="{{ route('admin.users.show', $item['id']) }}" class="text-purple-600 hover:text-purple-800 text-sm">View</a>
                                        <a href="{{ route('admin.users.edit', $item['id']) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                                        @elseif($type === 'artists')
                                        <a href="{{ route('admin.artists.show', $item['id']) }}" class="text-purple-600 hover:text-purple-800 text-sm">View</a>
                                        <a href="{{ route('admin.artists.edit', $item['id']) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                                        @elseif($type === 'organisers')
                                        <a href="{{ route('admin.organisers.show', $item['id']) }}" class="text-purple-600 hover:text-purple-800 text-sm">View</a>
                                        <a href="{{ route('admin.organisers.edit', $item['id']) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                                        @elseif($type === 'venues')
                                        <a href="{{ route('admin.venues.show', $item['id']) }}" class="text-purple-600 hover:text-purple-800 text-sm">View</a>
                                        <a href="{{ route('admin.venues.edit', $item['id']) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                                        @endif
                                        <button type="button" 
                                                class="text-amber-600 hover:text-amber-800 text-sm js-rename-entity"
                                                data-type="{{ $type }}"
                                                data-id="{{ $item['id'] }}"
                                                data-name="{{ $item['name'] }}"
                                                data-url="{{ route('admin.duplicates.rename', ['type' => $type, 'id' => $item['id']]) }}">
                                            Rename
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 px-6 py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No duplicates found</h3>
        <p class="mt-1 text-sm text-gray-500">All entity names are unique across the system.</p>
    </div>
    @endforelse
</div>

<!-- Rename Modal -->
<div id="rename-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="rename-modal-backdrop"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form id="rename-form" method="POST">
                @csrf
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-100">
                        <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Rename to Resolve Duplicate
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Enter a new unique name to differentiate this entity from others.
                            </p>
                        </div>
                        <div class="mt-4">
                            <label for="new_name" class="block text-sm font-medium text-gray-700 text-left">New Name</label>
                            <input type="text" name="new_name" id="new_name" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm form-input">
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
                        Rename
                    </button>
                    <button type="button" id="cancel-rename" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rename-modal');
    const form = document.getElementById('rename-form');
    const nameInput = document.getElementById('new_name');
    const backdrop = document.getElementById('rename-modal-backdrop');
    const cancelBtn = document.getElementById('cancel-rename');

    // Open modal
    document.querySelectorAll('.js-rename-entity').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;
            
            form.action = url;
            nameInput.value = name;
            modal.classList.remove('hidden');
            nameInput.focus();
            nameInput.select();
        });
    });

    // Close modal
    function closeModal() {
        modal.classList.add('hidden');
    }

    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
});
</script>
@endpush
@endsection


