/**
 * Add show/hide password (eye icon) toggle to all password inputs.
 */
function initPasswordToggles() {
  const eyeSvg = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
  const eyeOffSvg = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878a4.5 4.5 0 106.262 6.262M3.958 3.958a9.97 9.97 0 012.029-1.563C7.732 5.175 11.523 8 16 8c.716 0 1.41.078 2.078.225M3.958 3.958L20.042 20.042"/></svg>`;

  document.querySelectorAll('input[type="password"]').forEach((input) => {
    if (input.dataset.passwordToggle === 'done') return;
    input.dataset.passwordToggle = 'done';

    let container = input.parentElement;
    const hasRelative = container && getComputedStyle(container).position === 'relative';
    if (!hasRelative) {
      const wrapper = document.createElement('div');
      wrapper.className = 'relative';
      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);
      container = wrapper;
    }

    if (!input.classList.contains('pr-10') && !input.classList.contains('pr-12')) {
      input.classList.add('pr-10');
    }
    input.style.paddingRight = input.style.paddingRight || '2.5rem';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Show password');
    btn.className = 'absolute inset-y-0 right-0 flex items-center pr-3 z-10 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600';
    btn.innerHTML = eyeSvg;
    btn.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      btn.innerHTML = isPassword ? eyeOffSvg : eyeSvg;
    });
    container.appendChild(btn);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPasswordToggles);
} else {
  initPasswordToggles();
}

// Re-run when Alpine or other JS might have revealed new content (e.g. modals)
document.addEventListener('alpine:initialized', () => {
  setTimeout(initPasswordToggles, 0);
});
