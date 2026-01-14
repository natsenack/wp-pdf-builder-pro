/**
 * PDF Builder React - Bootstrap Script
 * Creates stub functions on window that get replaced by webpack bundle
 */

(function() {
    'use strict';

    // Gestionnaire d'erreurs ultra-précoce pour les erreurs de syntaxe des extensions
    // Doit être exécuté avant tout autre code pour capturer les erreurs de parsing
    var originalOnError = window.onerror;
    window.onerror = function(message, source, lineno, colno, error) {
        // Intercepter les erreurs de syntaxe des extensions Chrome
        if (typeof message === 'string' &&
            (message.includes('Unexpected token \'export\'') ||
             message.includes('Extension context invalidated') ||
             message.includes('A listener indicated an asynchronous response by returning true'))) {
            console.warn('⚠️ Erreur d\'extension interceptée très tôt:', message);
            return true; // Empêche l'erreur de remonter
        }

        // Appeler l'ancien gestionnaire si existant
        if (originalOnError) {
            return originalOnError(message, source, lineno, colno, error);
        }

        return false;
    };

    // Gestionnaire d'erreurs global pour les erreurs d'extensions de navigateur
    // Intercepte les erreurs courantes des extensions Chrome comme les contextes invalidés ou les canaux de messagerie fermés
    window.addEventListener('unhandledrejection', function(event) {
        const error = event.reason;
        if (error && typeof error.message === 'string' &&
            (error.message.includes('A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received') ||
             error.message.includes('Extension context invalidated.') ||
             error.message.includes('Unexpected token \'export\''))) {
            console.warn('⚠️ Erreur d\'extension interceptée et ignorée:', error.message);
            event.preventDefault(); // Empêche l'erreur de remonter
            return false; // Indique que l'erreur a été gérée
        }
    });

    // Gestionnaire d'erreurs global pour les erreurs synchrones d'extensions
    window.addEventListener('error', function(event) {
        const error = event.error || event.message;
        if (error && typeof error === 'string' &&
            (error.includes('A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received') ||
             error.includes('Extension context invalidated.') ||
             error.includes('Unexpected token \'export\''))) {
            console.warn('⚠️ Erreur d\'extension synchronisée interceptée et ignorée:', error);
            event.preventDefault(); // Empêche l'erreur de remonter
            return false; // Indique que l'erreur a été gérée
        }
    });

    // Create initialized flag
    var isInitialized = false;
    var initialized = {};

    // Create object with all required methods
    var createStub = function() {
        return {
            initPDFBuilderReact: function() { return false; },
            loadTemplate: function() { return false; },
            getEditorState: function() { return null; },
            setEditorState: function() { return false; },
            getCurrentTemplate: function() { return null; },
            exportTemplate: function() { return false; },
            saveTemplate: function() { return false; },
            registerEditorInstance: function() { return false; },
            resetAPI: function() { return false; },
            updateCanvasDimensions: function() { return false; }
        };
    };

    // Create initial stub
    window.pdfBuilderReact = createStub();
    Object.assign(initialized, window.pdfBuilderReact);

    // Check if webpack bundle has replaced the stub
    // A real implementation will have non-empty function bodies
    var checkRealModule = setInterval(function() {
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
            var fnStr = window.pdfBuilderReact.initPDFBuilderReact.toString();

            // Check if this is a real implementation (not a stub that just returns false)
            if (fnStr.indexOf('return false') === -1 && fnStr.indexOf('warn') === -1) {
                // Verify it's different from our stub
                var isReal = fnStr.length > 50; // Real function will be longer

                if (isReal) {
                    isInitialized = true;
                    clearInterval(checkRealModule);

                    // Dispatch ready event
                    try {
                        var event = new Event('pdfBuilderReactReady');
                        document.dispatchEvent(event);
                    } catch (e) {
                        console.error('[pdf-builder-wrap] Error dispatching event:', e);
                    }
                    return;
                }
            }
        }
    }, 50);

    // Force dispatch event after timeout
    setTimeout(function() {
        if (!isInitialized) {
            clearInterval(checkRealModule);
            // Still dispatch event so initialization can proceed with stub
            try {
                var event = new Event('pdfBuilderReactReady');
                document.dispatchEvent(event);
            } catch (e) {}
        }
    }, 5000);
})();
