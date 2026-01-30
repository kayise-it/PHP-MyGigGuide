/**
 * AJAX Search Component
 * Provides real-time search and filtering without page reload
 * Usage: Initialize with AjaxSearch.init()
 */

class AjaxSearch {
    constructor(config = {}) {
        this.form = config.formSelector ? document.querySelector(config.formSelector) : document.getElementById('ajax-search-form');
        this.resultsContainer = config.resultsSelector ? document.querySelector(config.resultsSelector) : document.getElementById('ajax-results');
        this.debounceDelay = config.debounceDelay || 500;
        this.debounceTimer = null;
        this.loadingClass = config.loadingClass || 'ajax-loading';
        
        if (!this.form) {
            console.warn('AjaxSearch: Form not found');
            return;
        }
        
        if (!this.resultsContainer) {
            console.warn('AjaxSearch: Results container not found, will search for it on first search');
            // Try to find it later when needed
            this.resultsContainer = null;
        }

        this.init();
    }

    init() {
        this.attachEventListeners();
        // Also wire up any "Clear Filters" triggers present on the page
        const clearEls = document.querySelectorAll('[data-clear-filters]');
        clearEls.forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                this.clearFilters();
            });
        });
    }

    attachEventListeners() {
        // Handle text inputs with debouncing
        const textInputs = this.form.querySelectorAll('input[type="text"], input[type="search"], input[type="date"]');
        textInputs.forEach(input => {
            input.addEventListener('input', (e) => this.handleTextInput(e));
        });

        // Handle select dropdowns (immediate search)
        const selectInputs = this.form.querySelectorAll('select');
        selectInputs.forEach(select => {
            select.addEventListener('change', (e) => this.performSearch());
        });

        // Handle pagination clicks
        this.attachPaginationListeners();

        // Prevent form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });
    }

    handleTextInput(event) {
        // Clear existing timer
        clearTimeout(this.debounceTimer);

        // Set new timer
        this.debounceTimer = setTimeout(() => {
            this.performSearch();
        }, this.debounceDelay);
    }

    performSearch() {
        // Find results container if not already set
        if (!this.resultsContainer) {
            this.resultsContainer = document.getElementById('ajax-results');
            if (!this.resultsContainer) {
                console.error('AjaxSearch: Results container not found');
                this.showError('Results container not found. Please refresh the page.');
                return;
            }
        }
        
        const formData = new FormData(this.form);
        const params = new URLSearchParams(formData);
        
        // Get the current URL without query parameters
        const baseUrl = window.location.pathname;
        const fullUrl = `${baseUrl}?${params.toString()}`;

        // Show loading state
        this.showLoading();

        // Perform AJAX request
        fetch(fullUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            // Parse the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Extract the results - try multiple selectors
            let newResults = doc.getElementById('ajax-results');
            if (!newResults) {
                // Try to find the table wrapper
                newResults = doc.querySelector('.bg-white.rounded-xl.border');
            }
            if (!newResults) {
                // Try to find any table
                newResults = doc.querySelector('table');
            }
            
            if (newResults) {
                // If we found the ajax-results div, use its innerHTML
                // Otherwise, wrap the content
                if (newResults.id === 'ajax-results') {
                    this.resultsContainer.innerHTML = newResults.innerHTML;
                } else {
                    // Wrap the found element in the ajax-results div structure
                    this.resultsContainer.innerHTML = `<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">${newResults.outerHTML}</div>`;
                }
                
                // Reattach pagination listeners
                this.attachPaginationListeners();
                
                // Reinitialize checkbox functionality if available
                if (typeof window.initCheckboxFunctionality === 'function') {
                    window.initCheckboxFunctionality();
                }
                
                // Dispatch custom event for other components that need to reinitialize
                window.dispatchEvent(new CustomEvent('ajax-results-updated', {
                    detail: { container: this.resultsContainer }
                }));
                
                // Update URL without page reload
                window.history.pushState({}, '', fullUrl);
                
                // Scroll to top of results smoothly
                this.resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                console.error('AjaxSearch: Could not find results in response');
                throw new Error('No results found in response');
            }
            
            this.hideLoading();
        })
        .catch(error => {
            console.error('Search error:', error);
            this.hideLoading();
            this.showError('An error occurred while searching. Please try again.');
        });
    }

    attachPaginationListeners() {
        // Find all pagination links within the results container
        const paginationLinks = this.resultsContainer.querySelectorAll('a[href*="page="], .pagination a');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                
                if (url && url !== '#') {
                    this.loadPage(url);
                }
            });
        });

        // Handle per-page changes
        const perPageSelects = this.resultsContainer.querySelectorAll('#per-page, select[onchange*="changePerPage"]');
        perPageSelects.forEach(select => {
            select.removeAttribute('onchange'); // Remove inline onchange
            select.addEventListener('change', (e) => {
                const formData = new FormData(this.form);
                const params = new URLSearchParams(formData);
                params.set('per_page', e.target.value);
                params.set('page', 1);
                
                const baseUrl = window.location.pathname;
                const fullUrl = `${baseUrl}?${params.toString()}`;
                this.loadPage(fullUrl);
            });
        });
    }

    loadPage(url) {
        this.showLoading();
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newResults = doc.getElementById('ajax-results');
            
            if (newResults) {
                this.resultsContainer.innerHTML = newResults.innerHTML;
                this.attachPaginationListeners();
                
                // Reinitialize checkbox functionality if available
                if (typeof window.initCheckboxFunctionality === 'function') {
                    window.initCheckboxFunctionality();
                }
                
                // Dispatch custom event for other components that need to reinitialize
                window.dispatchEvent(new CustomEvent('ajax-results-updated', {
                    detail: { container: this.resultsContainer }
                }));
                
                window.history.pushState({}, '', url);
                this.resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            this.hideLoading();
        })
        .catch(error => {
            console.error('Pagination error:', error);
            this.hideLoading();
        });
    }

    showLoading() {
        this.resultsContainer.classList.add(this.loadingClass);
        this.resultsContainer.style.opacity = '0.5';
        this.resultsContainer.style.pointerEvents = 'none';
    }

    hideLoading() {
        this.resultsContainer.classList.remove(this.loadingClass);
        this.resultsContainer.style.opacity = '1';
        this.resultsContainer.style.pointerEvents = 'auto';
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
        errorDiv.innerHTML = `<span class="block sm:inline">${message}</span>`;
        
        this.resultsContainer.insertBefore(errorDiv, this.resultsContainer.firstChild);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    clearFilters() {
        // Reset all form inputs
        const inputs = this.form.querySelectorAll('input[type="text"], input[type="search"], input[type="date"]');
        inputs.forEach(input => input.value = '');
        
        const selects = this.form.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
            // Trigger change event to ensure any custom components are updated
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        // Clear any hidden inputs that might store values
        const hiddenInputs = this.form.querySelectorAll('input[type="hidden"]');
        hiddenInputs.forEach(input => {
            if (input.name === 'genre' || input.name.includes('genre')) {
                input.value = '';
            }
        });
        
        // Perform search to show all results
        this.performSearch();
    }

    static init(config = {}) {
        const instance = new AjaxSearch(config);
        // expose globally for inline handlers fallback
        if (typeof window !== 'undefined') {
            window.ajaxSearchInstance = instance;
        }
        return instance;
    }
}

// Handle browser back/forward buttons
window.addEventListener('popstate', function(event) {
    location.reload();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AjaxSearch;
}


