/**
 * PDF Builder React - Initialization Script
 * Initializes the React editor when all dependencies are loaded
 */

(function(window) {
    'use strict';

    // Initialization Manager
    function ReactInitializer() {
        this.initialized = false;
        this.attempts = 0;
        this.maxAttempts = 50; // 5 seconds at 100ms intervals
        this.init();
    }

    ReactInitializer.prototype.init = function() {
        console.log('[PDF Builder] Starting React editor initialization...');

        // Check for required dependencies
        this.checkDependencies();

        // Start initialization check loop
        this.checkInterval = setInterval(function() {
            this.attempts++;

            if (this.checkDependenciesReady()) {
                this.initializeEditor();
                clearInterval(this.checkInterval);
            } else if (this.attempts >= this.maxAttempts) {
                console.error('[PDF Builder] Failed to initialize React editor - dependencies not ready after', this.maxAttempts * 100, 'ms');
                clearInterval(this.checkInterval);
                this.showErrorMessage();
            }
        }.bind(this), 100);
    };

    ReactInitializer.prototype.checkDependencies = function() {
        console.log('[PDF Builder] Checking dependencies...');

        // Check for jQuery
        if (typeof jQuery === 'undefined') {
            console.warn('[PDF Builder] jQuery not found');
        } else {
            console.log('[PDF Builder] ✓ jQuery found');
        }

        // Check for React wrapper
        if (typeof window.pdfBuilderReactWrapper === 'undefined') {
            console.warn('[PDF Builder] pdfBuilderReactWrapper not found');
        } else {
            console.log('[PDF Builder] ✓ pdfBuilderReactWrapper found');
        }

        // Check for React main
        if (typeof window.pdfBuilderReact === 'undefined') {
            console.warn('[PDF Builder] pdfBuilderReact not found');
        } else {
            console.log('[PDF Builder] ✓ pdfBuilderReact found');
        }

        // Check for data
        if (typeof window.pdfBuilderData === 'undefined') {
            console.warn('[PDF Builder] pdfBuilderData not found');
        } else {
            console.log('[PDF Builder] ✓ pdfBuilderData found');
        }

        // Check for notifications
        if (typeof window.pdfBuilderNotificationManager === 'undefined') {
            console.warn('[PDF Builder] pdfBuilderNotificationManager not found');
        } else {
            console.log('[PDF Builder] ✓ pdfBuilderNotificationManager found');
        }
    };

    ReactInitializer.prototype.checkDependenciesReady = function() {
        var ready = true;

        // Check if all required objects exist
        if (typeof jQuery === 'undefined') ready = false;
        if (typeof window.pdfBuilderReact === 'undefined') ready = false;
        if (typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') ready = false;
        if (typeof window.pdfBuilderData === 'undefined') ready = false;

        if (ready) {
            console.log('[PDF Builder] All dependencies ready for initialization');
        }

        return ready;
    };

    ReactInitializer.prototype.initializeEditor = function() {
        if (this.initialized) return;

        console.log('[PDF Builder] Initializing React editor...');

        try {
            // Call the React initialization function
            if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
                var result = window.pdfBuilderReact.initPDFBuilderReact(window.pdfBuilderData);

                if (result) {
                    this.initialized = true;
                    console.log('[PDF Builder] ✓ React editor initialized successfully');

                    // Show success notification
                    if (window.pdfBuilderNotificationManager) {
                        window.pdfBuilderNotificationManager.success('Éditeur PDF Builder chargé avec succès');
                    }

                    // Dispatch custom event
                    var event = new CustomEvent('pdfBuilderReactInitialized', {
                        detail: {
                            data: window.pdfBuilderData,
                            timestamp: Date.now()
                        }
                    });
                    document.dispatchEvent(event);

                } else {
                    console.error('[PDF Builder] React initialization returned false');
                    this.showErrorMessage();
                }
            } else {
                console.error('[PDF Builder] initPDFBuilderReact function not available');
                this.showErrorMessage();
            }

        } catch (error) {
            console.error('[PDF Builder] Error during React initialization:', error);
            this.showErrorMessage();
        }
    };

    ReactInitializer.prototype.showErrorMessage = function() {
        // Create error message element
        var errorDiv = document.createElement('div');
        errorDiv.id = 'pdf-builder-init-error';
        errorDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            max-width: 500px;
            text-align: center;
        `;

        errorDiv.innerHTML = `
            <h3>Erreur de chargement de l'éditeur PDF Builder</h3>
            <p>Les scripts nécessaires n'ont pas pu être chargés. Veuillez rafraîchir la page ou contacter le support technique.</p>
            <button onclick="window.location.reload()" style="margin-top: 10px; padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Rafraîchir la page</button>
        `;

        document.body.appendChild(errorDiv);

        // Show error notification
        if (window.pdfBuilderNotificationManager) {
            window.pdfBuilderNotificationManager.error('Erreur lors du chargement de l\'éditeur PDF Builder');
        }
    };

    // Emergency fallback - try to initialize after a delay
    ReactInitializer.prototype.emergencyInit = function() {
        setTimeout(function() {
            if (!this.initialized) {
                console.log('[PDF Builder] Attempting emergency initialization...');
                this.initializeEditor();
            }
        }.bind(this), 2000);
    };

    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            new ReactInitializer();
        });
    } else {
        new ReactInitializer();
    }

    // Emergency initialization after 3 seconds
    setTimeout(function() {
        if (window.pdfBuilderReact && !window.pdfBuilderReactInitialized) {
            console.log('[PDF Builder] Emergency initialization check...');
            if (window.pdfBuilderReact.initPDFBuilderReact) {
                window.pdfBuilderReact.initPDFBuilderReact(window.pdfBuilderData || {});
            }
        }
    }, 3000);

})(window);