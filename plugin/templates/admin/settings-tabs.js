/**
 * PDF Builder Settings Tabs Navigation
 * Handles tab switching and content display
 * Updated: 2025-12-03 01:35:00
 */

/**
 * This file was a duplicate of `assets/js/settings-tabs.js`.
 * To avoid duplication, it will now only act as a shim: if the canonical
 * `PDFBuilderTabsAPI` is not present, it logs a warning, otherwise it defers to it.
 */

(function() {
    'use strict';

    // Shim: do nothing if canonical manager present
    if (window && window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function') {
        // Nothing to do, main script will handle tabs
        console.log('PDF Builder: settings-tabs.js (template) shim loaded — canonical manager detected. No action.');
        return;
    }

    // Otherwise, fallback minimal manager
    console.warn('PDF Builder: settings-tabs.js (template) loaded but PDFBuilderTabsAPI is NOT present. Minimal fallback engaged.');
    const buttons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
    const contents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
    function switchTabFallback(tabId) {
        buttons.forEach(b => b.classList.remove('nav-tab-active'));
        contents.forEach(c => c.classList.remove('active'));
        const btn = document.querySelector('#pdf-builder-tabs [data-tab="' + tabId + '"]');
        const content = document.getElementById(tabId);
        if (btn) { btn.classList.add('nav-tab-active'); }
        if (content) { content.classList.add('active'); }
    }
    buttons.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        const id = btn.getAttribute('data-tab');
        switchTabFallback(id);
    }));
})();
            if (!tabId) return;

            this.log('Setting active tab:', tabId);

            // Update button states
            this.tabButtons.forEach(button => {
                const buttonTabId = button.getAttribute('data-tab');
                if (buttonTabId === tabId) {
                    button.classList.add('nav-tab-active');
                    button.setAttribute('aria-selected', 'true');
                } else {
                    button.classList.remove('nav-tab-active');
                    button.setAttribute('aria-selected', 'false');
                }
            });

            // Update content states
            this.tabContents.forEach(content => {
                if (content.id === tabId) {
                    if (animate) {
                        this.fadeIn(content);
                    } else {
                        content.classList.add('active');
                        content.style.display = 'block';
                    }
                    content.setAttribute('aria-hidden', 'false');
                } else {
                    if (animate) {
                        this.fadeOut(content);
                    } else {
                        content.classList.remove('active');
                        content.style.display = 'none';
                    }
                    content.setAttribute('aria-hidden', 'true');
                }
            });

            // Update active tab
            this.activeTab = tabId;

            // Store in localStorage
            this.storeActiveTab(tabId);

            // Update URL hash
            this.updateHash(tabId);

            // Dispatch event
            document.dispatchEvent(new CustomEvent('pdfBuilderTabChanged', {
                detail: { tabId: tabId, manager: this }
            }));

            this.log('Active tab set to:', tabId);
        }

        fadeIn(element) {
            element.style.opacity = '0';
            element.style.display = 'block';
            element.classList.add('active');

            setTimeout(() => {
                element.style.transition = `opacity ${CONFIG.animationDuration}ms ease`;
                element.style.opacity = '1';
            }, 10);

            setTimeout(() => {
                element.style.transition = '';
            }, CONFIG.animationDuration);
        }

        fadeOut(element) {
            element.style.transition = `opacity ${CONFIG.animationDuration}ms ease`;
            element.style.opacity = '0';

            setTimeout(() => {
                element.classList.remove('active');
                element.style.display = 'none';
                element.style.transition = '';
                element.style.opacity = '';
            }, CONFIG.animationDuration);
        }

        handleKeyboardNavigation(e) {
            const activeButton = this.tabsContainer.querySelector('.nav-tab-active');
            if (!activeButton) return;

            const buttons = Array.from(this.tabButtons);
            const currentIndex = buttons.indexOf(activeButton);

            let newIndex = currentIndex;

            switch (e.key) {
                case 'ArrowLeft':
                    newIndex = currentIndex > 0 ? currentIndex - 1 : buttons.length - 1;
                    break;
                case 'ArrowRight':
                    newIndex = currentIndex < buttons.length - 1 ? currentIndex + 1 : 0;
                    break;
                case 'Home':
                    newIndex = 0;
                    break;
                case 'End':
                    newIndex = buttons.length - 1;
                    break;
                default:
                    return;
            }

            e.preventDefault();
            const newButton = buttons[newIndex];
            const newTabId = newButton.getAttribute('data-tab');
            if (newTabId) {
                this.setActiveTab(newTabId, true);
                newButton.focus();
            }
        }

        getStoredActiveTab() {
            try {
                return localStorage.getItem(CONFIG.storageKey);
            } catch (e) {
                return null;
            }
        }

        storeActiveTab(tabId) {
            try {
                localStorage.setItem(CONFIG.storageKey, tabId);
            } catch (e) {
                // localStorage not available
            }
        }

        getDefaultActiveTab() {
            // Try to get from URL hash first
            const hashTab = this.getTabFromHash();
            if (hashTab) return hashTab;

            // Otherwise, get the first available tab
            const firstButton = this.tabButtons[0];
            return firstButton ? firstButton.getAttribute('data-tab') : null;
        }

        getTabFromHash() {
            const hash = window.location.hash.substring(1); // Remove #
            if (hash && this.isValidTabId(hash)) {
                return hash;
            }
            return null;
        }

        updateHash(tabId) {
            if (window.history.replaceState) {
                const newUrl = window.location.pathname + window.location.search + '#' + tabId;
                window.history.replaceState(null, null, newUrl);
            }
        }

        isValidTabId(tabId) {
            return Array.from(this.tabContents).some(content => content.id === tabId);
        }

        // Public API methods
        switchToTab(tabId) {
            this.setActiveTab(tabId, true);
        }

        getActiveTab() {
            return this.activeTab;
        }

        destroy() {
            // Clean up event listeners if needed
            this.initialized = false;
            this.log('PDF Builder Tabs Manager destroyed');
        }

        log(...args) {
            if (CONFIG.debug) {
                console.log('🔄 PDF Builder Tabs:', ...args);
            }
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        console.log('🔄 PDF Builder Tabs: DOM ready, initializing...');

        // Create global instance
        window.PDF_BUILDER_TABS = new PDFBuilderTabsManager();

        // Mark as initialized for fallback detection
        window.PDF_BUILDER_TABS_INITIALIZED = true;

        // Exposer l'API globale pour interop
        try {
            window.PDFBuilderTabsAPI = window.PDFBuilderTabsAPI || {};
            window.PDFBuilderTabsAPI.switchToTab = (tabId) => window.PDF_BUILDER_TABS && window.PDF_BUILDER_TABS.switchToTab ? window.PDF_BUILDER_TABS.switchToTab(tabId) : null;
            window.PDFBuilderTabsAPI.getActiveTab = () => window.PDF_BUILDER_TABS && window.PDF_BUILDER_TABS.getActiveTab ? window.PDF_BUILDER_TABS.getActiveTab() : null;
        } catch(e) {
            console.log('PDF Builder: Impossible d\'exposer l\'API globale', e && e.message ? e.message : e);
        }

        console.log('🔄 PDF Builder Tabs: Initialization complete');
    });

    // Handle AJAX form submissions within tabs
    $(document).on('submit', '.tab-content form', function(e) {
        const form = $(this);
        const tabContent = form.closest('.tab-content');
        const submitButton = form.find('input[type="submit"], button[type="submit"]');

        // Show loading state
        if (submitButton.length) {
            submitButton.prop('disabled', true).val('Sauvegarde...');
        }

        // Add loading class to form
        form.addClass('loading');

        // Let the form submit normally, but prepare for response
        setTimeout(function() {
            if (submitButton.length) {
                submitButton.prop('disabled', false).val(submitButton.data('original-value') || 'Sauvegarder');
            }
            form.removeClass('loading');
        }, 2000);
    });

    // Handle settings save success/error
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url && settings.url.indexOf('admin-ajax.php') !== -1) {
            console.log('🔄 PDF Builder Tabs: AJAX completed', xhr.status);

            // Re-enable submit buttons
            $('input[type="submit"], button[type="submit"]').prop('disabled', false);
        }
    });

})(jQuery);