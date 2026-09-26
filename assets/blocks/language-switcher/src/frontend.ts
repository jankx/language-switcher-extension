/**
 * Language Switcher Frontend TypeScript
 * Position engine + dropdown open/close (mirrors the mini cart dropdown)
 */

interface DropdownPosition {
    alignRight: boolean;
    alignTop: boolean;
}

class LanguageSwitcherPositionEngine {
    private dropdowns: NodeListOf<Element>;

    constructor() {
        this.dropdowns = document.querySelectorAll('.language-switcher-dropdown-wrapper');
        this.init();
    }

    private init(): void {
        this.dropdowns.forEach((dropdown) => {
            this.setupDropdown(dropdown as HTMLElement);
        });

        // Re-calculate on window resize
        window.addEventListener('resize', () => {
            this.dropdowns.forEach((dropdown) => {
                this.adjustDropdownPosition(dropdown as HTMLElement);
            });
        });
    }

    private setupDropdown(wrapper: HTMLElement): void {
        if (wrapper.dataset.lsBound === '1') {
            return;
        }
        wrapper.dataset.lsBound = '1';

        const dropdown = wrapper.querySelector('.language-switcher-dropdown') as HTMLElement;
        const menu = wrapper.querySelector('.language-switcher-dropdown-menu') as HTMLElement;

        if (!dropdown || !menu) return;

        // Track if dropdown is open
        let isOpen = false;
        let isPositioned = false;

        // Calculate position first time
        const ensurePositioned = () => {
            if (!isPositioned) {
                this.adjustDropdownPosition(wrapper);
                isPositioned = true;
            }
        };

        const setOpen = (open: boolean) => {
            isOpen = open;
            if (open) {
                ensurePositioned(); // Calculate position before showing
                menu.classList.add('is-open');
            } else {
                menu.classList.remove('is-open');
            }
            dropdown.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        // Toggle dropdown on trigger click (click to open, same as the mini cart)
        dropdown.addEventListener('click', (e) => {
            e.preventDefault();
            setOpen(!isOpen);
        });

        // Close via the panel close button (like the mini cart close button)
        const closeButton = wrapper.querySelector('[data-ls-close]');
        if (closeButton) {
            closeButton.addEventListener('click', (e) => {
                e.preventDefault();
                setOpen(false);
            });
        }

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target as Node) && isOpen) {
                setOpen(false);
            }
        });

        // Escape closes the dropdown (same as the mini cart)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen) {
                setOpen(false);
            }
        });

        // Adjust position on focus
        dropdown.addEventListener('focus', () => {
            ensurePositioned();
        });
    }

    private adjustDropdownPosition(wrapper: HTMLElement): void {
        const menu = wrapper.querySelector('.language-switcher-dropdown-menu') as HTMLElement;
        if (!menu) return;

        const position = this.calculateOptimalPosition(wrapper, menu);

        // Reset classes
        menu.classList.remove('align-right', 'align-left', 'align-top', 'align-bottom');

        // Apply position classes
        if (position.alignRight) {
            menu.classList.add('align-right');
        } else {
            menu.classList.add('align-left');
        }

        if (position.alignTop) {
            menu.classList.add('align-top');
        } else {
            menu.classList.add('align-bottom');
        }

        // Mark as positioned to allow overflow
        wrapper.classList.add('is-positioned');
    }

    private calculateOptimalPosition(wrapper: HTMLElement, menu: HTMLElement): DropdownPosition {
        const wrapperRect = wrapper.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Check horizontal overflow
        const rightSpace = viewportWidth - wrapperRect.right;
        const leftSpace = wrapperRect.left;
        const menuWidth = menuRect.width || 340; // Fallback width

        // Check vertical overflow
        const bottomSpace = viewportHeight - wrapperRect.bottom;
        const topSpace = wrapperRect.top;
        const menuHeight = menuRect.height || 200; // Fallback height

        return {
            // Align right if not enough space on the left and more space on right
            alignRight: rightSpace < menuWidth && leftSpace > rightSpace,
            // Align top (show above) if not enough space below
            alignTop: bottomSpace < menuHeight && topSpace > bottomSpace
        };
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        new LanguageSwitcherPositionEngine();
    });
} else {
    new LanguageSwitcherPositionEngine();
}

// Re-initialize on AJAX content load
document.addEventListener('content-loaded', function() {
    new LanguageSwitcherPositionEngine();
});

// Handle dynamic content (for themes that support it)
if (typeof wp !== 'undefined' && wp.domReady) {
    wp.domReady(function() {
        new LanguageSwitcherPositionEngine();
    });
}

// Export for external use
(window as any).LanguageSwitcherPositionEngine = LanguageSwitcherPositionEngine;
