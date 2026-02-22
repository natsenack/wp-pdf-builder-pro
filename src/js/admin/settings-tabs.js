/**
 * PDF Builder Pro - Settings Tabs JavaScript
 * Handles tab navigation and content switching
 */



// Wait for jQuery to be available before defining API
function initializePDFBuilderTabsAPI() {
    if (typeof jQuery === 'undefined') {
        
        setTimeout(initializePDFBuilderTabsAPI, 50);
        return;
    }

    

    // Define API immediately for early access
    window.PDFBuilderTabsAPI = {
        switchToTab: function(tabId) {
            // Remove active class from all tabs
            jQuery('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');

            // Add active class to target tab
            jQuery(`.nav-tab-wrapper .nav-tab[href*="${tabId}"]`).addClass('nav-tab-active');

            // Since content is handled by PHP, we don't need to show/hide elements
            // Just trigger custom event for tab change
            jQuery(document).trigger('pdfBuilderTabChanged', [tabId]);
        },

        toggleAdvancedSection: function() {
            
            const $advancedSection = jQuery('#advanced-section');
            const $toggleIcon = jQuery('#advanced-toggle');

            
            
            

            if ($advancedSection.is(':visible')) {
                $advancedSection.slideUp();
                $toggleIcon.text('▶');
                
            } else {
                $advancedSection.slideDown();
                $toggleIcon.text('▼');
                
            }
        },

        resetTemplatesStatus: function() {
            // Reset templates status - implementation can be added as needed
            
        },

        getCurrentTab: function() {
            return jQuery('.nav-tab-wrapper .nav-tab.nav-tab-active').attr('href').substring(1);
        }
    };

    

    // Trigger custom event to signal API is ready
    jQuery(document).trigger('PDFBuilderTabsAPIReady');
    

    // Debug: check if jQuery is available and event was triggered
    
    

    (function($) {
        'use strict';

        // Initialize when document is ready
        $(document).ready(function() {
            

            // Initialize tab navigation
            initializeTabNavigation();

            // Handle URL hash for direct tab access
            handleUrlHash();

            
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
}

// Start initialization
initializePDFBuilderTabsAPI();

