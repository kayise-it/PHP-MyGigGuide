@props([
    'name' => 'artists',
    'id' => 'artists',
    'values' => [], // array of selected artist ids
    'placeholder' => 'Select artists...',
    'class' => '',
])

@php
    $artists = \App\Models\Artist::orderBy('stage_name')->get(['id','stage_name','genre','real_name']);
    $componentId = $id ?: 'artists_'.Str::random(6);
    $wrapperId = $componentId.'_wrapper';
    $searchId = $componentId.'_search';
    $menuId = $componentId.'_menu';
    $chipsId = $componentId.'_chips';
    $hiddenId = $componentId.'_hidden';
    // Precompute JSON-safe data to avoid complex expressions inside @json()
    $artistData = $artists
        ->map(function ($a) {
            return [
                'id' => (string) $a->id,
                'name' => $a->stage_name,
                'genre' => $a->genre,
                'real' => $a->real_name,
            ];
        })
        ->values();
@endphp

<div id="{{ $wrapperId }}" class="relative {{ $class }}">
    <div class="w-full flex items-center justify-between px-4 py-3 border-2 border-purple-300 rounded-xl">
        <div id="{{ $chipsId }}" class="flex flex-wrap gap-2">
            @foreach((array)$values as $aid)
                @php $a = $artists->firstWhere('id', $aid); @endphp
                @if($a)
                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm" data-id="{{ $a->id }}">
                    {{ $a->stage_name }}
                    <button type="button" class="remove-artist text-purple-600 hover:text-purple-800" data-id="{{ $a->id }}">×</button>
                </span>
                @endif
            @endforeach
            <span class="text-gray-400" data-placeholder>{{ empty($values) ? $placeholder : '' }}</span>
        </div>
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>

    <!-- Panel -->
    <div class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl hidden" id="{{ $componentId }}_panel">
        <div class="p-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input id="{{ $searchId }}" type="text" placeholder="Search artists by stage or real name..." autocomplete="off" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </div>
                <button type="button" class="px-3 py-2 text-sm rounded-lg border border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100">Filters</button>
            </div>
        </div>
        <div id="{{ $menuId }}" class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"></div>
    </div>

    <div id="{{ $hiddenId }}">
        @foreach((array)$values as $aid)
            <input type="hidden" name="{{ $name }}[]" value="{{ $aid }}" />
        @endforeach
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const data = @json($artistData);
        const wrapper = document.getElementById('{{ $wrapperId }}');
        const trigger = wrapper.querySelector('.border-2');
        const panel = document.getElementById('{{ $componentId }}_panel');
        const search = document.getElementById('{{ $searchId }}');
        const menu = document.getElementById('{{ $menuId }}');
        const chips = document.getElementById('{{ $chipsId }}');
        const hiddenContainer = document.getElementById('{{ $hiddenId }}');

        const selected = new Set(@json(array_map('strval', (array)$values)));

        function refreshPlaceholder() {
            const ph = chips.querySelector('[data-placeholder]');
            ph.textContent = selected.size ? '' : '{{ $placeholder }}';
        }

        function removeChip(id) {
            const chip = chips.querySelector(`span[data-id="${id}"]`);
            chip && chip.remove();
            const input = hiddenContainer.querySelector(`input[value="${id}"]`);
            input && input.remove();
            selected.delete(String(id));
            refreshPlaceholder();
        }

        chips.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-artist');
            if (!btn) return;
            removeChip(btn.dataset.id);
        });

        function addArtist(artist) {
            if (selected.has(String(artist.id))) return;
            selected.add(String(artist.id));
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm';
            chip.dataset.id = artist.id;
            chip.innerHTML = `${artist.name}<button type="button" class="remove-artist text-purple-600 hover:text-purple-800" data-id="${artist.id}">×</button>`;
            chips.insertBefore(chip, chips.querySelector('[data-placeholder]'));
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '{{ $name }}[]';
            input.value = artist.id;
            hiddenContainer.appendChild(input);
            refreshPlaceholder();
        }

        function render(items) {
            menu.innerHTML = '';
            items.slice(0, 100).forEach(a => {
                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'w-full text-left px-4 py-3 hover:bg-gray-50';
                row.innerHTML = `<div class="text-sm font-medium text-gray-900">${a.name}</div><div class="text-xs text-gray-500">${a.real ? a.real + ' • ' : ''}${a.genre || ''}</div>`;
                row.addEventListener('click', () => { addArtist(a); panel.classList.add('hidden'); });
                menu.appendChild(row);
            });
        }

        function filter(q) {
            const s = q.trim().toLowerCase();
            const items = !s ? data : data.filter(a => (a.name && a.name.toLowerCase().includes(s)) || (a.real && a.real.toLowerCase().includes(s)) || (a.genre && a.genre.toLowerCase().includes(s)));
            render(items);
        }

        trigger.addEventListener('click', () => {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) { search.focus(); filter(search.value); }
        });
        search.addEventListener('input', (e) => filter(e.target.value));
        document.addEventListener('click', (e) => { if (!wrapper.contains(e.target)) panel.classList.add('hidden'); });

        refreshPlaceholder();
    });
    </script>
</div>


