@props([
    'name' => 'genre',
    'id' => 'genre',
    'value' => null,
    'placeholder' => 'Select a genre',
    'required' => false,
    'class' => '',
    'top' => null, // optional array of top genre names to pin
])

@php
    $allGenres = \App\Models\Genre::where('is_active', true)
        ->orderBy('name')
        ->get(['id','name','slug']);

    $topGenres = $top ?? [
        'Pop','Rock','Hip Hop','Electronic','Jazz','R&B','Reggae','Country','Classical','Soul'
    ];

    $initial = $value ? (string) $value : '';
    $componentId = $id ?: 'genre_'.Str::random(6);
    $wrapperId = $componentId.'_wrapper';
    $inputId = $componentId.'_input';
    $menuId = $componentId.'_menu';
    $hiddenId = $componentId.'_hidden';
@endphp

<div id="{{ $wrapperId }}" class="relative {{ $class }}">
    <input type="hidden" id="{{ $hiddenId }}" name="{{ $name }}" value="{{ e($initial) }}">

    <!-- Trigger -->
    <button type="button" id="{{ $componentId }}_trigger" class="w-full flex items-center justify-between px-4 py-3 border-2 border-purple-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
        <div class="flex items-center gap-3 text-gray-700">
            <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-2v13"/></svg>
            <span class="text-sm">{{ $initial ? $initial : $placeholder }}</span>
        </div>
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <!-- Panel -->
    <div id="{{ $componentId }}_panel" class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl hidden">
        <div class="p-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input id="{{ $inputId }}" type="text" placeholder="Search genres..." autocomplete="off" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </div>
                <button type="button" class="px-3 py-2 text-sm rounded-lg border border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100">Filters</button>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($topGenres as $tg)
                <button type="button" class="genre-chip px-2.5 py-1 text-xs rounded-full border border-purple-200 bg-purple-50 text-purple-800 hover:bg-purple-100" data-value="{{ $tg }}">{{ $tg }}</button>
                @endforeach
            </div>
        </div>
        <div id="{{ $menuId }}" class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <!-- items injected via JS -->
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const all = @json($allGenres->map(fn($g) => ['id'=>$g->id,'name'=>$g->name,'slug'=>$g->slug]));
        const input = document.getElementById('{{ $inputId }}');
        const menu = document.getElementById('{{ $menuId }}');
        const hidden = document.getElementById('{{ $hiddenId }}');
        const wrapper = document.getElementById('{{ $wrapperId }}');
        const trigger = document.getElementById('{{ $componentId }}_trigger');
        const panel = document.getElementById('{{ $componentId }}_panel');

        function render(items) {
            if (!items.length) { menu.classList.add('hidden'); menu.innerHTML = ''; return; }
            menu.innerHTML = '';
            items.slice(0, 100).forEach(it => {
                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'w-full text-left px-4 py-3 hover:bg-gray-50';
                row.innerHTML = `<div class="text-sm font-medium text-gray-900">${it.name}</div><div class="text-xs text-gray-500">Genre</div>`;
                row.addEventListener('click', () => {
                    input.value = it.name;
                    hidden.value = it.name;
                    panel.classList.add('hidden');
                    // update trigger label
                    trigger.querySelector('span').textContent = it.name;
                });
                menu.appendChild(row);
            });
        }

        function filter(q) {
            const s = q.trim().toLowerCase();
            if (!s) { render(all); return; }
            render(all.filter(g => g.name.toLowerCase().includes(s)));
        }

        trigger.addEventListener('click', () => {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                input.focus();
                render(all);
            }
        });
        input.addEventListener('focus', () => render(all));
        input.addEventListener('input', (e) => filter(e.target.value));
        document.addEventListener('click', (e) => { if (!wrapper.contains(e.target)) panel.classList.add('hidden'); });

        wrapper.querySelectorAll('.genre-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const v = chip.dataset.value;
                input.value = v;
                hidden.value = v;
                trigger.querySelector('span').textContent = v;
                panel.classList.add('hidden');
            });
        });
    });
    </script>
</div>


