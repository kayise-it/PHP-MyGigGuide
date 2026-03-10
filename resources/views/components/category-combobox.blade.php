@props([
    'name' => 'categories',
    'id' => 'categories',
    'values' => [], // array of selected category ids
    'placeholder' => 'Select categories...',
    'class' => '',
])

@php
    $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get(['id','name','slug','description','color','icon']);
    $componentId = $id ?: 'categories_'.Str::random(6);
    $wrapperId = $componentId.'_wrapper';
    $searchId = $componentId.'_search';
    $menuId = $componentId.'_menu';
    $chipsId = $componentId.'_chips';
    $hiddenId = $componentId.'_hidden';
    // Precompute JSON-safe data to avoid complex expressions inside @json()
    $categoryData = $categories
        ->map(function ($c) {
            return [
                'id' => (string) $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'color' => $c->color,
                'icon' => $c->icon,
            ];
        })
        ->values();
@endphp

<div id="{{ $wrapperId }}" class="relative {{ $class }}">
    <div class="w-full flex items-center justify-between px-4 py-3 border-2 border-purple-300 rounded-xl">
        <div id="{{ $chipsId }}" class="flex flex-wrap gap-2">
            @foreach((array)$values as $cid)
                @php $c = $categories->firstWhere('id', $cid); @endphp
                @if($c)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-sm" 
                      data-id="{{ $c->id }}"
                      style="background-color: {{ $c->color ?? '#e9d5ff' }}; color: {{ $c->color ? '#ffffff' : '#581c87' }};">
                    @if($c->icon)
                        <span>{{ $c->icon }}</span>
                    @endif
                    {{ $c->name }}
                    <button type="button" class="remove-category hover:opacity-75" data-id="{{ $c->id }}" style="color: inherit;">×</button>
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
                    <input id="{{ $searchId }}" type="text" placeholder="Search categories by name or description..." autocomplete="off" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </div>
            </div>
        </div>
        <div id="{{ $menuId }}" class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"></div>
    </div>

    <div id="{{ $hiddenId }}">
        @foreach((array)$values as $cid)
            <input type="hidden" name="{{ $name }}[]" value="{{ $cid }}" />
        @endforeach
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const data = @json($categoryData);
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
            const btn = e.target.closest('.remove-category');
            if (!btn) return;
            removeChip(btn.dataset.id);
        });

        function addCategory(category) {
            if (selected.has(String(category.id))) return;
            selected.add(String(category.id));
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full text-sm';
            chip.dataset.id = category.id;
            const bgColor = category.color || '#e9d5ff';
            const textColor = category.color ? '#ffffff' : '#581c87';
            chip.style.backgroundColor = bgColor;
            chip.style.color = textColor;
            
            const iconHtml = category.icon ? `<span>${category.icon}</span>` : '';
            chip.innerHTML = `${iconHtml}${category.name}<button type="button" class="remove-category hover:opacity-75" data-id="${category.id}" style="color: inherit;">×</button>`;
            chips.insertBefore(chip, chips.querySelector('[data-placeholder]'));
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '{{ $name }}[]';
            input.value = category.id;
            hiddenContainer.appendChild(input);
            refreshPlaceholder();
        }

        function render(items) {
            menu.innerHTML = '';
            items.slice(0, 100).forEach(c => {
                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center gap-3';
                
                const iconDiv = document.createElement('div');
                iconDiv.className = 'flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center';
                iconDiv.style.backgroundColor = c.color || '#e9d5ff';
                iconDiv.style.color = c.color ? '#ffffff' : '#581c87';
                
                if (c.icon) {
                    iconDiv.innerHTML = `<span class="text-2xl">${c.icon}</span>`;
                } else {
                    iconDiv.innerHTML = `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>`;
                }
                
                const textDiv = document.createElement('div');
                textDiv.innerHTML = `<div class="text-sm font-medium text-gray-900">${c.name}</div><div class="text-xs text-gray-500">${c.description || c.slug}</div>`;
                
                row.appendChild(iconDiv);
                row.appendChild(textDiv);
                row.addEventListener('click', () => { addCategory(c); panel.classList.add('hidden'); });
                menu.appendChild(row);
            });
        }

        function filter(q) {
            const s = q.trim().toLowerCase();
            const items = !s ? data : data.filter(c => 
                (c.name && c.name.toLowerCase().includes(s)) || 
                (c.description && c.description.toLowerCase().includes(s)) || 
                (c.slug && c.slug.toLowerCase().includes(s))
            );
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

