/**
 * PDF Builder Pro - Settings Tabs JavaScript
 * Handles tab navigation and content switching
 */

// Define API immediately for early access
window.PDFBuilderTabsAPI = {
    switchToTab: function(tabId) {
        // Remove active class from all tabs
        $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');

        // Add active class to target tab
        $(`.nav-tab-wrapper .nav-tab[href*="${tabId}"]`).addClass('nav-tab-active');

        // Since content is handled by PHP, we don't need to show/hide elements
        // Just trigger custom event for tab change
        $(document).trigger('pdfBuilderTabChanged', [tabId]);
    },

    toggleAdvancedSection: function() {
        const $advancedSection = $('.pdf-advanced-settings');
        const $toggleButton = $('.toggle-advanced-pdf');

        if ($advancedSection.is(':visible')) {
            $advancedSection.slideUp();
            $toggleButton.text('Afficher les paramètres avancés');
        } else {
            $advancedSection.slideDown();
            $toggleButton.text('Masquer les paramètres avancés');
        }
    },

    resetTemplatesStatus: function() {
        // Reset templates status - implementation can be added as needed
        console.log('[PDF Builder] Resetting templates status');
    },

    getCurrentTab: function() {
        return $('.nav-tab-wrapper .nav-tab.nav-tab-active').attr('href').substring(1);
    }
};

console.log('PDFBuilderTabsAPI defined at script load time');

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('[PDF Builder Settings] Initializing tabs...');

        // Initialize tab navigation
        initializeTabNavigation();

        // Handle URL hash for direct tab access
        handleUrlHash();

        console.log('[PDF Builder Settings] Tabs initialized');
    });

    /**
     * Initialize tab navigation
     */
    function initializeTabNavigation() {
        $('.nav-tab-wrapper .nav-tab').on('click', function(e) {
            // Remove preventDefault to allow normal navigation
            // e.preventDefault();

            const tab = $(this);
            const href = tab.attr('href');

            // Extract tab parameter from URL
            let tabId = '';
            if (href.includes('tab=')) {
                const url = new URL(href, window.location.origin);
                tabId = url.searchParams.get('tab') || '';
            }

            if (tabId) {
                // Update URL hash for consistency
                history.replaceState(null, null, '#' + tabId);

                // Switch to tab (visual feedback)
                window.PDFBuilderTabsAPI.switchToTab(tabId);
            }
        });
    }

    /**
     * Handle URL hash for direct tab access
     */
    function handleUrlHash() {
        // Check URL parameters first (server-side tabs)
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            window.PDFBuilderTabsAPI.switchToTab(tabParam);
            return;
        }

        // Fallback to hash for legacy support
        const hash = window.location.hash.substring(1);
        if (hash) {
            let tabId = hash;
            if (hash.includes('tab=')) {
                const hashParams = new URLSearchParams(hash);
                tabId = hashParams.get('tab') || hash;
            }
            window.PDFBuilderTabsAPI.switchToTab(tabId);
        }
    }

})(jQuery);