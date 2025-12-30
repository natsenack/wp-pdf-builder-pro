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

        // Forcer le rechargement de tous les scripts PDF Builder
        const scripts = document.querySelectorAll('script[src*="pdf-builder"]');
        scripts.forEach(script => {
            const src = script.src;
            script.remove();

            // Recharger le script avec un paramètre de cache busting
            const newScript = document.createElement('script');
            newScript.src = src + (src.includes('?') ? '&' : '?') + '_force_reload=' + Date.now();
            document.head.appendChild(newScript);
            console.log('🔄 [DEBUG] Rechargement forcé:', src);
        });

        // Forcer le rechargement de la page après un délai
        setTimeout(() => {
            window.location.reload(true);
        }, 1000);
    };

    // Ajouter un bouton dans la console
    console.log('🎯 [DEBUG] Fonction forcePDFBuilderReload() disponible');
    console.log('💡 [DEBUG] Tapez forcePDFBuilderReload() pour forcer le rechargement');

})();
