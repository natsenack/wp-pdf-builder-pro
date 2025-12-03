/**
 * PDF Builder Settings Tabs Navigation
 * Handles tab switching and content display
 * Updated: 2025-12-03 01:35:00
 */

(function($) {
    'use strict';

    // Configuration
    const CONFIG = {
        debug: true, // Set to false in production
        animationDuration: 200,
        storageKey: 'pdf_builder_active_tab'
    };

    // Main Tabs Manager Class
    class PDFBuilderTabsManager {
        constructor() {
            this.tabsContainer = null;
            this.contentContainer = null;
            this.tabButtons = null;
            this.tabContents = null;
            this.activeTab = null;
            this.initialized = false;

            this.init();
        }

        init() {
            if (this.initialized) return;

            this.log('Initializing PDF Builder Tabs Manager...');

            // Find containers
            this.tabsContainer = document.getElementById('pdf-builder-tabs');
            this.contentContainer = document.getElementById('pdf-builder-tab-content');

            if (!this.tabsContainer || !this.contentContainer) {
                this.log('ERROR: Required containers not found', {
                    tabsContainer: !!this.tabsContainer,
                    contentContainer: !!this.contentContainer
                });
                return;
            }

            // Get tab elements
            this.tabButtons = this.tabsContainer.querySelectorAll('.nav-tab');
            this.tabContents = this.contentContainer.querySelectorAll('.tab-content');

            this.log('Found elements:', {
                tabButtons: this.tabButtons.length,
                tabContents: this.tabContents.length
            });

            if (this.tabButtons.length === 0 || this.tabContents.length === 0) {
                this.log('ERROR: No tab buttons or contents found');
                return;
            }

            // Determine initial active tab
            this.activeTab = this.getStoredActiveTab() || this.getDefaultActiveTab();

            // Bind events
            this.bindEvents();

            // Set initial state
            this.setActiveTab(this.activeTab, false);

            this.initialized = true;
            this.log('PDF Builder Tabs Manager initialized successfully');

            // Dispatch custom event
            document.dispatchEvent(new CustomEvent('pdfBuilderTabsReady', {
                detail: { manager: this }
            }));
        }

        bindEvents() {
                // Click events via delegation - robust if DOM changes
                const delegatedHandler = (e) => {
                    const anchor = e.target.closest && e.target.closest('.nav-tab');
                        const tabsRoot = document.getElementById('pdf-builder-tabs');
                        if (!anchor || !tabsRoot || !tabsRoot.contains(anchor)) return;

                    if (anchor.tagName === 'A' && anchor.getAttribute('href') && anchor.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                    }

                    const tabId = anchor.getAttribute('data-tab');
                    if (!tabId) {
                        this.log('Delegate: no data-tab on element', anchor);
                        return;
                    }

                    this.log('Delegate: Tab clicked', tabId);
                    this.setActiveTab(tabId, true);
                };

                // Add capturing delegation so our handler runs before many other non-capturing handlers
                try {
                    if (!window.PDFBuilderTabsDelegationInstalled) {
                        document.removeEventListener('click', delegatedHandler, true);
                        document.addEventListener('click', delegatedHandler, true);
                        window.PDFBuilderTabsDelegationInstalled = true;
                    }
                } catch(e) {
                    this.tabsContainer.removeEventListener('click', delegatedHandler, true);
                    this.tabsContainer.addEventListener('click', delegatedHandler, true);
                }

                // Setup mutation observer to refresh references when DOM changes
                const observer = new MutationObserver((mutations) => {
                    let reset = false;
                    for (const m of mutations) {
                        if (m.type === 'childList' && (m.addedNodes.length || m.removedNodes.length)) {
                            reset = true; break;
                        }
                        if (m.type === 'attributes' && (m.attributeName === 'class' || m.attributeName === 'data-tab')) {
                            reset = true; break;
                        }
                    }
                    if (reset) {
                        this.tabButtons = this.tabsContainer.querySelectorAll('.nav-tab');
                        this.tabContents = this.contentContainer.querySelectorAll('.tab-content');
                        this.log('MutationObserver: refresh des sélecteurs d\'onglets');
                    }
                });
                try {
                    observer.observe(this.tabsContainer, { childList: true, subtree: true, attributes: true });
                    observer.observe(this.contentContainer, { childList: true, subtree: true, attributes: true });
                } catch(e) {
                    this.log('MutationObserver erreur:', e && e.message ? e.message : e);
                }

            // Keyboard navigation
            this.tabsContainer.addEventListener('keydown', (e) => {
                this.handleKeyboardNavigation(e);
            });

            // Hash change (for direct links)
            window.addEventListener('hashchange', () => {
                const hashTab = this.getTabFromHash();
                if (hashTab && hashTab !== this.activeTab) {
                    this.setActiveTab(hashTab, true);
                }
            });

            this.log('Event listeners bound');
        }

        setActiveTab(tabId, animate = true) {
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