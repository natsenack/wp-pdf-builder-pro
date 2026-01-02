// PDF Builder React Init Helper
// Version: 1.0.0
// Date: 2026-01-02

(function() {
    'use strict';

    // console.log('[PDF Builder Init] Initializing PDF Builder React...');

    // Attendre que le DOM soit prêt
    document.addEventListener('DOMContentLoaded', function() {
        // console.log('[PDF Builder Init] DOM ready, checking for React...');

        // Vérifier que pdfBuilderReact existe
        if (typeof window.pdfBuilderReact === 'undefined') {
            console.error('[PDF Builder Init] pdfBuilderReact not found on window');
            return;
        }

        // Vérifier que la fonction d'initialisation existe
        if (typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
            console.error('[PDF Builder Init] initPDFBuilderReact function not found');
            return;
        }

        // console.log('[PDF Builder Init] React functions available, initializing...');

        try {
            // Initialiser React
            const result = window.pdfBuilderReact.initPDFBuilderReact();

            // console.log('[PDF Builder Init] React initialization result:', result);

            // Masquer le loader après un court délai
            setTimeout(function() {
                const loader = document.getElementById('pdf-builder-loader');
                const editor = document.getElementById('pdf-builder-editor-container');

                if (loader && editor) {
                    loader.style.display = 'none';
                    editor.style.display = 'block';
                    // console.log('[PDF Builder Init] Loader hidden, editor shown');
                }
            }, 500);

        } catch (error) {
            console.error('[PDF Builder Init] Error initializing React:', error);

            // Fallback: masquer le loader même en cas d'erreur
            setTimeout(function() {
                const loader = document.getElementById('pdf-builder-loader');
                const editor = document.getElementById('pdf-builder-editor-container');

                if (loader && editor) {
                    loader.style.display = 'none';
                    editor.style.display = 'block';
                    console.warn('[PDF Builder Init] Showing editor despite initialization error');
                }
            }, 1000);
        }
    });

    // console.log('[PDF Builder Init] Init helper loaded successfully');

})();