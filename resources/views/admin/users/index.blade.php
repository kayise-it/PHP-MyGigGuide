@extends('layouts.admin')

@section('title', 'User Management - My Gig Guide')
@section('page-title', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Users</h2>
            <p class="mt-1 text-sm text-gray-600">Manage user accounts and permissions</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add User
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <form method="GET" id="ajax-search-form" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name, username, or email..."
                    class="form-input"
                >
            </div>
            <div class="sm:w-48">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select name="role" id="role" class="form-input">
                    <option value="">All Roles</option>
                    @foreach(\Laratrust\Models\Role::all() as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['search', 'role']))
            <div class="flex items-end">
                <a href="#" data-clear-filters class="text-purple-600 hover:text-purple-800 px-4 py-2 flex items-center font-medium transition-colors duration-200">
                    Clear Filters
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div id="ajax-results">
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" class="sortable-header inline-flex items-center gap-1" data-key="name" aria-label="Sort by user">
                                User
                                <svg class="sort-caret hidden w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3l5 7H5l5-7z"/></svg>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" class="sortable-header inline-flex items-center gap-1" data-key="is_active" aria-label="Sort by status">
                                Status
                                <svg class="sort-caret hidden w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3l5 7H5l5-7z"/></svg>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button type="button" class="sortable-header inline-flex items-center gap-1" data-key="created_at_ts" aria-label="Sort by joined date">
                                Joined
                                <svg class="sort-caret hidden w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3l5 7H5l5-7z"/></svg>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody" class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50" data-name="{{ Str::lower($user->name) }}" data-is_active="{{ $user->is_active ? 1 : 0 }}" data-created_at_ts="{{ $user->created_at->timestamp }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 flex items-center justify-center">
                                        <span class="text-white text-sm font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <!-- <div class="text-sm text-gray-500">{{ $user->email }}</div> -->
                                        <div class="text-xs text-gray-400">{{ '@' . $user->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($user->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-4">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="text-purple-600 hover:text-purple-800 js-user-modal"
                                       data-user-id="{{ $user->id }}">
                                        View
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800">
                                        Edit
                                    </a>
                                    <button type="button"
                                            class="text-red-600 hover:text-red-800 js-delete-user"
                                            data-url="{{ route('admin.users.destroy', $user) }}"
                                            data-user-row>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new user.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
    </div>
</div>

@push('scripts')
<script>
    // Inline AJAX search fallback to avoid missing /js/ajax-search.js
    (function(){
        class InlineAjaxSearch {
            constructor() {
                this.form = document.getElementById('ajax-search-form');
                this.results = document.getElementById('ajax-results');
                this.debounceTimer = null;
                if (!this.form || !this.results) return;
                this.bind();
            }
            bind(){
                this.form.querySelectorAll('input[type="text"],input[type="search"],input[type="date"]').forEach(inp=>{
                    inp.addEventListener('input', ()=>{
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(()=>this.search(), 400);
                    });
                });
                this.form.querySelectorAll('select').forEach(sel=>{
                    sel.addEventListener('change', ()=>this.search());
                });
                this.form.addEventListener('submit', (e)=>{ e.preventDefault(); this.search(); });
                document.querySelectorAll('[data-clear-filters]').forEach(el=>{
                    el.addEventListener('click', (e)=>{ e.preventDefault(); this.clearFilters(); });
                });
            }
            urlWithParams(extra={}){
                const fd = new FormData(this.form);
                Object.entries(extra).forEach(([k,v])=>fd.set(k,v));
                const params = new URLSearchParams(fd);
                return `${window.location.pathname}?${params.toString()}`;
            }
            search(){
                const url = this.urlWithParams();
                this.loading(true);
                fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'text/html' }})
                    .then(r=>r.text())
                    .then(html=>{
                        const doc = new DOMParser().parseFromString(html,'text/html');
                        const repl = doc.getElementById('ajax-results');
                        if (repl) {
                            this.results.innerHTML = repl.innerHTML;
                            window.history.pushState({}, '', url);
                            this.attachPerPage();
                            if (window.attachUserModalLinks) window.attachUserModalLinks();
                        }
                    })
                    .finally(()=>this.loading(false));
            }
            attachPerPage(){
                const per = this.results.querySelector('#per-page');
                if (per) {
                    per.addEventListener('change', (e)=>{
                        const url = this.urlWithParams({ per_page: e.target.value, page: 1 });
                        this.load(url);
                    });
                }
                this.results.querySelectorAll('a[href*="page="]').forEach(a=>{
                    a.addEventListener('click', (e)=>{ e.preventDefault(); this.load(a.href); });
                });
            }
            load(url){
                this.loading(true);
                fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'text/html' }})
                    .then(r=>r.text())
                    .then(html=>{
                        const doc = new DOMParser().parseFromString(html,'text/html');
                        const repl = doc.getElementById('ajax-results');
                        if (repl) {
                            this.results.innerHTML = repl.innerHTML;
                            window.history.pushState({}, '', url);
                            this.attachPerPage();
                            if (window.attachUserModalLinks) window.attachUserModalLinks();
                        }
                    })
                    .finally(()=>this.loading(false));
            }
            clearFilters(){
                this.form.querySelectorAll('input[type="text"],input[type="search"],input[type="date"]').forEach(i=>i.value='');
                this.form.querySelectorAll('select').forEach(s=>{ s.selectedIndex = 0; s.dispatchEvent(new Event('change', { bubbles:true })); });
                this.search();
            }
            loading(on){
                this.results.style.opacity = on ? '0.5' : '1';
                this.results.style.pointerEvents = on ? 'none' : 'auto';
            }
        }
        document.addEventListener('DOMContentLoaded', function(){
            window.ajaxSearchInstance = new InlineAjaxSearch();
        });
    })();

    let ajaxSearchInstance;
    document.addEventListener('DOMContentLoaded', function() {
        ajaxSearchInstance = window.ajaxSearchInstance;

        window.attachUserModalLinks = function () {
            const links = document.querySelectorAll('.js-user-modal');
            if (!window.AdminModal || !links.length) return;

            links.forEach(link => {
                link.addEventListener('click', function (e) {
                    // If JS disabled, this handler never runs and link works normally.
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    if (!url) return;
                    window.AdminModal.openFromUrl(url, {
                        loadingText: 'Loading user details...'
                    });
                }, { once: true });
            });
        };

        window.attachUserModalLinks();

        // Client-side sorting (no reload)
        const tbody = document.getElementById('users-tbody');
        const headers = document.querySelectorAll('.sortable-header');
        let currentSortKey = null;
        let currentDir = 'asc';

        function updateCarets(activeHeader) {
            headers.forEach(h => {
                const caret = h.querySelector('.sort-caret');
                if (!caret) return;
                caret.classList.add('hidden');
                caret.classList.remove('transform', 'rotate-180');
            });
            if (activeHeader) {
                const c = activeHeader.querySelector('.sort-caret');
                if (c) {
                    c.classList.remove('hidden');
                    if (currentDir === 'asc') {
                        c.classList.add('transform', 'rotate-180');
                    }
                }
            }
        }

        function sortRows(key, dir) {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                const av = a.dataset[key] || '';
                const bv = b.dataset[key] || '';
                // Numeric compare when both are numbers
                const an = Number(av);
                const bn = Number(bv);
                let cmp;
                if (!Number.isNaN(an) && !Number.isNaN(bn) && (String(an) === av || String(bn) === bv)) {
                    cmp = an - bn;
                } else {
                    cmp = av.localeCompare(bv);
                }
                return dir === 'asc' ? cmp : -cmp;
            });
            // Re-append in order
            rows.forEach(r => tbody.appendChild(r));
        }

        headers.forEach(h => {
            h.addEventListener('click', () => {
                const key = h.dataset.key;
                if (currentSortKey === key) {
                    currentDir = currentDir === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSortKey = key;
                    currentDir = 'asc';
                }
                sortRows(currentSortKey, currentDir);
                updateCarets(h);
            });
        });

        // AJAX delete handling
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta ? tokenMeta.content : '';
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.js-delete-user');
            if (!btn) return;
            e.preventDefault();
            const url = btn.getAttribute('data-url');
            const row = btn.closest('tr');
            if (!url || !row) return;
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ _method: 'DELETE' })
                });
                if (!res.ok) throw new Error('Failed');
                // Optimistically remove the row
                row.parentNode.removeChild(row);
            } catch (err) {
                alert('Failed to delete user. Please try again.');
            }
        });

        // AJAX change email
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.js-change-email');
            if (!btn) return;
            e.preventDefault();
            const url = btn.getAttribute('data-url');
            const row = btn.closest('tr');
            const current = btn.getAttribute('data-current-email') || '';
            const newEmail = prompt('Enter new email address:', current);
            if (!newEmail) return;
            const sync = confirm('Also update linked artist/organiser contact email to this address?');
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ _method: 'PATCH', email: newEmail, sync_related: sync ? 1 : 0 })
                });
                const data = await res.json().catch(()=>({success:false}));
                if (!res.ok || !data.success) throw new Error('Failed');
                // Update email cell text
                const emailEl = row.querySelector('td .text-sm.text-gray-500');
                if (emailEl) emailEl.textContent = newEmail;
                btn.setAttribute('data-current-email', newEmail);
                alert('Email updated. Note: email verification has been reset for this user.');
            } catch (err) {
                alert('Failed to update email. Ensure it is unique and valid.');
            }
        });
    });
</script>
@endpush
@endsection

