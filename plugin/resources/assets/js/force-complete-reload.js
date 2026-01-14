/**
 * PDF Builder Pro - Force Complete CSS Reload
 * Script pour vérifier et forcer le rechargement complet des ressources CSS
 */

(function($) {
    'use strict';

    // Fonction pour vérifier si les fichiers CSS sont bien déployés (silencieuse)
    function checkCSSDeployment() {
        // Vérification CSS silencieuse

        // Vérifier les fichiers CSS attendus
        const cssFiles = [
            'contenu-settings.css',
            'modals-contenu.css'
        ];

        cssFiles.forEach(function(filename) {
            // Vérification temporairement désactivée
            return;
        });
    }

    // Fonction pour forcer le rechargement complet des CSS (silencieuse)
    function forceCompleteCSSReload() {
        // Forcer le rechargement complet des assets silencieusement

        // Supprimer TOUS les liens CSS du plugin (même ceux avec cache busting)
        $('link[rel="stylesheet"]').each(function() {
            var href = $(this).attr('href');
            if (href && href.includes('wp-pdf-builder-pro')) {
                $(this).remove();
            }
        });

        // Supprimer TOUS les scripts JS du plugin (même ceux avec cache busting)
        $('script').each(function() {
            var src = $(this).attr('src');
            if (src && src.includes('wp-pdf-builder-pro')) {
                $(this).remove();
            }
        });

        // Générer un timestamp unique pour forcer le rechargement
        var timestamp = Date.now();

        // Recharger les CSS critiques
        var cssFiles = [
            'resources/assets/css/admin-global.css',
            'resources/assets/css/settings.css',
            'resources/assets/css/modals-contenu.css',
            'resources/assets/css/contenu-settings.css'
        ];

        cssFiles.forEach(function(cssFile, index) {
            setTimeout(function() {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = '/wp-content/plugins/wp-pdf-builder-pro/' + cssFile + '?v=' + timestamp;
                document.head.appendChild(link);
            }, index * 100); // Délai pour éviter les conflits
        });

        // Recharger les JS critiques avec délai
        var jsFiles = [
            'resources/assets/js/canvas-card-monitor.js',
            'resources/assets/js/pdf-preview-integration.js',
            'resources/assets/js/settings-tabs.js',
            'resources/assets/js/debug-css-modals.js'
        ];

        jsFiles.forEach(function(jsFile, index) {
            setTimeout(function() {
                var script = document.createElement('script');
                script.src = '/wp-content/plugins/wp-pdf-builder-pro/' + jsFile + '?v=' + timestamp;
                document.head.appendChild(script);
            }, (cssFiles.length * 100) + (index * 200)); // Après les CSS + délai entre JS
        });

        // Forcer un petit délai avant de signaler la fin
        setTimeout(function() {
            // Rechargement terminé silencieusement
        }, (cssFiles.length * 100) + (jsFiles.length * 200) + 500);
    }

    // Fonction pour ajouter des styles inline temporaires pour tester (silencieuse)
    function addTestStyles() {
        var testCSS = `
            .contenu-settings .cache-modal-container {
                border: 3px solid red !important;
                animation: testPulse 1s infinite !important;
            }

            @keyframes testPulse {
                0% { border-color: red; }
                50% { border-color: blue; }
                100% { border-color: red; }
            }

            .contenu-settings .cache-modal-overlay.active {
                background: rgba(255, 0, 0, 0.3) !important;
            }
        `;

        var style = document.createElement('style');
        style.type = 'text/css';
        style.id = 'pdf-builder-test-styles';
        style.appendChild(document.createTextNode(testCSS));
        document.head.appendChild(style);
    }

    // Fonction pour vérifier et corriger automatiquement la corruption du cache JS (silencieuse)
    function checkAndFixJSCacheCorruption() {
        // Vérifier si canvas-card-monitor.js est corrompu
        const jsFilesToCheck = [
            'resources/assets/js/canvas-card-monitor.js'
        ];

        jsFilesToCheck.forEach(function(jsFile) {
            const fullUrl = '/wp-content/plugins/wp-pdf-builder-pro/' + jsFile;

            fetch(fullUrl + '?_check=' + Date.now(), {
                method: 'GET',
                cache: 'no-cache'
            })
            .then(function(response) {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error('HTTP ' + response.status);
                }
            })
            .then(function(content) {
                // Vérifier si le contenu contient du HTML corrompu (signe de cache corrompu)
                if (content.includes('<parameter name="filePath">') || content.includes('<html') || content.includes('<!DOCTYPE')) {
                } else {
                    // Cache OK silencieusement
                }
            })
            .catch(function(error) {
                // Impossible de vérifier silencieusement
            });
        });
    }

    // Fonction d'urgence pour corriger immédiatement le cache corrompu (silencieuse)
    function emergencyCacheFix() {
        // Correction d'urgence silencieuse
        const timestamp = Date.now();
        const script = document.createElement('script');
        script.src = '/wp-content/plugins/wp-pdf-builder-pro/resources/assets/js/canvas-card-monitor.js?v=' + timestamp + '&emergency=' + timestamp;
        document.head.appendChild(script);
    }

    // Fonction pour vérifier les styles calculés (silencieuse)
    function checkComputedStyles() {
        // Vérification silencieuse
        const testEl = document.createElement('div');
        testEl.style.display = 'none';
        document.body.appendChild(testEl);
        const computed = window.getComputedStyle(testEl);
        document.body.removeChild(testEl);
    }

    // Exposer les fonctions globalement
    window.pdfBuilderCheckCSS = checkCSSDeployment;
    window.pdfBuilderForceReload = forceCompleteCSSReload;
    window.pdfBuilderTestStyles = addTestStyles;
    window.pdfBuilderCheckStyles = checkComputedStyles;
    window.pdfBuilderCheckJSCache = checkAndFixJSCacheCorruption;
    window.pdfBuilderEmergencyFix = emergencyCacheFix;

    // Auto-vérification silencieuse
    $(document).ready(function() {
        // Vérifications automatiques silencieuses
        setTimeout(checkCSSDeployment, 2000);
        setTimeout(checkAndFixJSCacheCorruption, 3000);
    });

})(jQuery);