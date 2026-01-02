// PDF Builder - Force Complete Reload
// Script pour vérifier et forcer le rechargement des CSS

(function($) {
    'use strict';

    // Configuration
    const config = {
        checkInterval: 30000, // 30 secondes
        maxChecks: 10,
        excludedFiles: [
            'contenu-settings.css',
            'modals-contenu.css'
        ]
    };

    // État du système
    let checkCount = 0;
    let isChecking = false;

    // Fonctions utilitaires
    function log(message, type = 'info') {
        const prefix = '🔍 PDF Builder:';
        switch(type) {
            case 'error':
                console.error(`${prefix} ${message}`);
                break;
            case 'warning':
                console.warn(`${prefix} ${message}`);
                break;
            default:
                console.log(`${prefix} ${message}`);
        }
    }

    // Vérifier si un fichier est exclu
    function isExcluded(filename) {
        return config.excludedFiles.some(excluded => filename.includes(excluded));
    }

    // Vérifier le déploiement CSS
    function checkCSSDeployment() {
        if (isChecking) return;
        isChecking = true;

        log('Vérification du déploiement CSS...');

        // Trouver tous les liens CSS du plugin
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"][href*="wp-pdf-builder-pro"]');

        cssLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (!href) return;

            // Extraire le nom du fichier
            const filename = href.split('/').pop().split('?')[0];

            if (isExcluded(filename)) {
                log(`Vérification CSS temporairement désactivée pour: ${filename}`);
                return;
            }

            // Vérifier si le fichier est accessible
            fetch(href, { method: 'HEAD' })
                .then(response => {
                    if (!response.ok) {
                        log(`CSS manquant ou inaccessible: ${filename}`, 'error');
                    } else {
                        log(`CSS OK: ${filename}`);
                    }
                })
                .catch(error => {
                    log(`Erreur vérification CSS ${filename}: ${error.message}`, 'error');
                });
        });

        isChecking = false;
    }

    // Forcer le rechargement complet des CSS
    window.pdfBuilderForceReload = function() {
        log('Forçage du rechargement COMPLET des assets...');

        // Supprimer TOUS les liens CSS du plugin (même ceux avec cache busting)
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"][href*="wp-pdf-builder-pro"]');
        cssLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                log(`Suppression CSS: ${href.split('/').pop()}`);
                link.remove();
            }
        });

        // Supprimer TOUS les scripts JS du plugin
        const jsScripts = document.querySelectorAll('script[src*="wp-pdf-builder-pro"]');
        jsScripts.forEach(script => {
            const src = script.getAttribute('src');
            if (src) {
                log(`Suppression JS: ${src.split('/').pop()}`);
                script.remove();
            }
        });

        // Forcer le rechargement de la page
        setTimeout(() => {
            window.location.reload(true);
        }, 100);
    };

    // Ajouter les fonctions globales
    window.pdfBuilderCheckCSS = checkCSSDeployment;
    window.pdfBuilderForceReload = window.pdfBuilderForceReload;

    // Démarrer les vérifications automatiques
    $(document).ready(function() {
        log('CSS Debug Tools chargées:');
        log('   - pdfBuilderCheckCSS() : Vérifier déploiement');
        log('   - pdfBuilderForceReload() : Forcer rechargement complet');

        // Vérification initiale
        setTimeout(checkCSSDeployment, 2000);

        // Vérifications périodiques
        setInterval(() => {
            checkCount++;
            if (checkCount <= config.maxChecks) {
                checkCSSDeployment();
            }
        }, config.checkInterval);
    });

})(jQuery);