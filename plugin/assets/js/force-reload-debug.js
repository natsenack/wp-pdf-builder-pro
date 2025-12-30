(function() {
    'use strict';

    // Fonction pour forcer le rechargement complet
    window.forcePDFBuilderReload = function() {
        console.log('🔄 [DEBUG] Forçage du rechargement complet des assets PDF Builder...');

        // Vider localStorage et sessionStorage
        try {
            localStorage.clear();
            sessionStorage.clear();
            console.log('✅ [DEBUG] Cache navigateur vidé');
        } catch(e) {
            console.warn('⚠️ [DEBUG] Impossible de vider le cache navigateur:', e);
        }

        // Nettoyer les variables globales PDF Builder pour éviter les conflits
        const globalVarsToClean = [
            'CANVAS_DEFAULT_VALUES',
            'PDFPreviewAPI',
            'PDFEditorPreviewIntegration',
            'pdfBuilderData',
            'pdfBuilderCanvasSettings',
            'PDFBuilder',
            'PDF_Builder_React',
            'PDF_Builder_Canvas',
            'PDF_Builder_API'
        ];

        globalVarsToClean.forEach(varName => {
            try {
                if (window[varName]) {
                    delete window[varName];
                    console.log('🧹 [DEBUG] Variable globale nettoyée:', varName);
                }
            } catch(e) {
                console.warn('⚠️ [DEBUG] Impossible de nettoyer la variable:', varName, e);
            }
        });

        // Supprimer tous les scripts PDF Builder existants
        const scripts = document.querySelectorAll('script[src*="pdf-builder"]');
        console.log('🗑️ [DEBUG] Suppression de', scripts.length, 'scripts PDF Builder existants');

        scripts.forEach(script => {
            script.remove();
        });

        // Attendre un peu pour que les suppressions soient effectives
        setTimeout(() => {
            console.log('🔄 [DEBUG] Rechargement des scripts PDF Builder...');

            // Liste des scripts critiques à recharger dans le bon ordre
            const criticalScripts = [
                'force-reload-debug.js',
                'js-syntax-check.js',
                'notifications.js',
                'ajax-throttle.js',
                'pdf-builder-wrap.js',
                'pdf-builder-react.bundle.js',
                'runtime.bundle.js',
                'vendors.bdb99cf4aa1bb604ae5a.js'
            ];

            criticalScripts.forEach(scriptName => {
                const scriptPath = 'https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/assets/js/' + scriptName;
                const newScript = document.createElement('script');

                // Ajouter cache busting
                newScript.src = scriptPath + '?nocache=' + Date.now() + '&v=' + Math.random();

                // Gérer les erreurs de chargement
                newScript.onerror = function() {
                    console.warn('⚠️ [DEBUG] Échec du chargement:', scriptName);
                };

                newScript.onload = function() {
                    console.log('✅ [DEBUG] Script rechargé:', scriptName);
                };

                document.head.appendChild(newScript);
            });

            // Recharger la page après un délai plus long pour laisser les scripts se charger
            setTimeout(() => {
                console.log('🔄 [DEBUG] Rechargement de la page...');
                window.location.reload(true);
            }, 2000);

        }, 500);
    };

    // Ajouter un bouton dans la console
    console.log('🎯 [DEBUG] Fonction forcePDFBuilderReload() disponible');
    console.log('💡 [DEBUG] Tapez forcePDFBuilderReload() pour forcer le rechargement');

})();
