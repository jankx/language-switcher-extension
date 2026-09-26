/**
 * Language Switcher Frontend TypeScript
 * Dropdown toggle/close behavior – copied from currency-switcher.js
 */

// Close all open dropdowns (mirrors mini cart close behavior)
function closeAllDropdowns(): void {
    document.querySelectorAll('.ls-dropdown-wrapper.is-open').forEach(function (wrapper) {
        wrapper.classList.remove('is-open');
        var btn = wrapper.querySelector('.ls-dropdown');
        if (btn) {
            btn.setAttribute('aria-expanded', 'false');
        }
    });
}

// Handle dropdown button toggle (click to open/close)
document.addEventListener('click', function (e) {
    var dropdownToggle = (e.target as Element).closest('.ls-dropdown');
    if (dropdownToggle) {
        var wrapper = dropdownToggle.closest('.ls-dropdown-wrapper');
        if (wrapper) {
            e.preventDefault();
            var isOpen = wrapper.classList.contains('is-open');
            // Close any other open dropdowns first
            closeAllDropdowns();
            if (!isOpen) {
                wrapper.classList.add('is-open');
                dropdownToggle.setAttribute('aria-expanded', 'true');
            }
            return;
        }
    }

    // Close via the panel close button (like the mini cart close button)
    if ((e.target as Element).closest('[data-ls-close]')) {
        closeAllDropdowns();
        return;
    }

    // Close dropdown when clicking outside
    if (!(e.target as Element).closest('.ls-dropdown-wrapper')) {
        closeAllDropdowns();
    }
});

// Escape closes the dropdown (same as the mini cart)
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeAllDropdowns();
    }
});
