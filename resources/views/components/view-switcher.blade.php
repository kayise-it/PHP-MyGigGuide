@props(['currentView' => 'cards'])

<div class="flex items-center space-x-2">
    <span class="{{ $siteBrand->viewSwitcherLabelClass() }}">View:</span>
    
    <!-- Cards View Button -->
    <button 
        onclick="switchView('cards')"
        class="{{ $currentView === 'cards' ? $siteBrand->viewSwitcherActiveClass() : $siteBrand->viewSwitcherInactiveClass() }}"
        title="Card View"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
    </button>
    
    <!-- Table View Button -->
    <button 
        onclick="switchView('table')"
        class="{{ $currentView === 'table' ? $siteBrand->viewSwitcherActiveClass() : $siteBrand->viewSwitcherInactiveClass() }}"
        title="Table View"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
    </button>
</div>

<script>
function switchView(view) {
    localStorage.setItem('events_view', view);
    const url = new URL(window.location);
    url.searchParams.set('view', view);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const urlView = urlParams.get('view');
    const storedView = localStorage.getItem('events_view');
    
    if (urlView) {
        localStorage.setItem('events_view', urlView);
    } else if (storedView && !urlView) {
        const url = new URL(window.location);
        url.searchParams.set('view', storedView);
        window.history.replaceState({}, '', url.toString());
    }
});
</script>
