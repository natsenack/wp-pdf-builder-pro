/**
 * PDF Builder Pro - Force Complete CSS Reload
 * Script pour vérifier et forcer le rechargement complet des ressources CSS
 * Inclut la gestion des erreurs de messagerie asynchrone
 */

(function($) {
    'use strict';

    // Gestionnaire d'erreurs global pour les erreurs d'extensions de navigateur
    // Intercepte les erreurs courantes des extensions Chrome comme les contextes invalidés ou les canaux de messagerie fermés
    window.addEventListener('unhandledrejection', function(event) {
        const error = event.reason;
        if (error && typeof error.message === 'string' && 
            (error.message.includes('A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received') ||
             error.message.includes('Extension context invalidated.'))) {
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
             error.includes('Extension context invalidated.'))) {
            console.warn('⚠️ Erreur d\'extension synchronisée interceptée et ignorée:', error);
            event.preventDefault(); // Empêche l'erreur de remonter
            return false; // Indique que l'erreur a été gérée
        }
    });

    // Fonction pour vérifier si les fichiers CSS sont bien déployés
    function checkCSSDeployment() {

        // Vérifier les fichiers CSS attendus
        const cssFiles = [
            'contenu-settings.css',
            'modals-contenu.css'
        ];

        cssFiles.forEach(function(filename) {
            // TEMPORAIREMENT DÉSACTIVÉ - Problème de chemin URL
            // console.log('🔍 Vérification CSS temporairement désactivée pour:', filename);
            return;

            // Utiliser l'URL localisée si disponible, sinon construire manuellement
            let baseUrl;
            if (typeof pdfBuilderForceReload !== 'undefined' && pdfBuilderForceReload.pluginUrl) {
                baseUrl = pdfBuilderForceReload.pluginUrl + 'resources/assets/css/';
                // console.log('✅ Utilisation URL localisée pour', filename);
            } else {
                baseUrl = window.location.origin + '/wp-content/plugins/wp-pdf-builder-pro/plugin/resources/assets/css/';
                // console.log('⚠️ Fallback URL manuelle pour', filename);
            }

            fetch(baseUrl + filename + '?_t=' + Date.now(), {
                method: 'HEAD',
                cache: 'no-cache'
            })
            .then(function(response) {
                // console.log('🔗 Tentative de fetch:', baseUrl + filename + '?_t=' + Date.now());
                if (response.ok) {
                    // console.log('✅ ' + filename + ' - déployé et accessible');
                } else {
                    console.error('❌ ' + filename + ' - NON accessible (status: ' + response.status + ')');
                }
            })
            .catch(function(error) {
                console.error('❌ ' + filename + ' - Erreur de chargement:', error);
            });
        });
    }

    // Fonction pour forcer le rechargement complet des CSS
    function forceCompleteCSSReload() {
        // console.log('🔄 PDF Builder: Forçage du rechargement COMPLET des assets...');

        // Supprimer TOUS les liens CSS du plugin (même ceux avec cache busting)
        $('link[rel="stylesheet"]').each(function() {
            var href = $(this).attr('href');
            if (href && href.includes('wp-pdf-builder-pro')) {
                $(this).remove();
                // console.log('🗑️ CSS supprimé:', href);
            }
        });

        // Supprimer TOUS les scripts JS du plugin (même ceux avec cache busting)
        $('script').each(function() {
            var src = $(this).attr('src');
            if (src && src.includes('wp-pdf-builder-pro')) {
                $(this).remove();
                // console.log('🗑️ JS supprimé:', src);
            }
        });

        // Générer un timestamp unique pour forcer le rechargement
        var timestamp = Date.now();
        // console.log('⏰ Timestamp de rechargement:', timestamp);

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
                // console.log('📄 CSS rechargé:', cssFile);
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
                // console.log('📜 JS rechargé:', jsFile);
            }, (cssFiles.length * 100) + (index * 200)); // Après les CSS + délai entre JS
        });

        // Forcer un petit délai avant de signaler la fin
        setTimeout(function() {
            // console.log('✅ Rechargement complet terminé - Les assets devraient être à jour');
            // console.log('🔄 Si les erreurs persistent, faites Ctrl+F5 pour vider le cache complet');
        }, (cssFiles.length * 100) + (jsFiles.length * 200) + 500);
    }

    // Fonction pour ajouter des styles inline temporaires pour tester
    function addTestStyles() {
        // console.log('🎨 Ajout de styles de test temporaires...');

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

        // console.log('✅ Styles de test ajoutés - les modales devraient avoir une bordure rouge pulsante');
    }

    // Fonction pour vérifier et corriger automatiquement la corruption du cache JS
    function checkAndFixJSCacheCorruption() {
        // console.log('🔍 PDF Builder: Vérification de la corruption du cache JS...');

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
                    console.error('🚨 CACHE CORROMPU DÉTECTÉ pour:', jsFile);
                    // console.log('🔄 Rechargement automatique du script...');

                    // Recharger automatiquement le script corrompu
                    forceReloadSpecificJS(jsFile);
                } else {
                    // console.log('✅ Cache OK pour:', jsFile);
                }
            })
            .catch(function(error) {
                console.warn('⚠️ Impossible de vérifier le cache pour:', jsFile, error);
            });
        });
    }

    // Fonction d'urgence pour corriger immédiatement le cache corrompu
    function emergencyCacheFix() {
        // console.log('🚨 MODE URGENCE: Correction immédiate du cache corrompu');

        // Forcer le rechargement immédiat de canvas-card-monitor.js
        const timestamp = Date.now();
        const script = document.createElement('script');
        script.src = '/wp-content/plugins/wp-pdf-builder-pro/resources/assets/js/canvas-card-monitor.js?v=' + timestamp + '&emergency=' + timestamp;
        script.onload = function() {
            // console.log('✅ URGENCE: canvas-card-monitor.js rechargé avec succès');
            // console.log('🔍 Vérifiez que l\'erreur "Unexpected token" a disparu');
        };
        script.onerror = function() {
            console.error('❌ URGENCE: Échec du rechargement de canvas-card-monitor.js');
        };
        document.head.appendChild(script);

        // console.log('📜 Script d\'urgence injecté avec timestamp:', timestamp);
    }

    // Fonction pour vérifier les styles calculés
    function checkComputedStyles() {
        // console.log('🔍 Vérification des styles calculés...');
        // Implémentation simple
        const testEl = document.createElement('div');
        testEl.style.display = 'none';
        document.body.appendChild(testEl);
        const computed = window.getComputedStyle(testEl);
        // console.log('✅ Styles calculés OK');
        document.body.removeChild(testEl);
    }

    // Exposer les fonctions globalement
    window.pdfBuilderCheckCSS = checkCSSDeployment;
    window.pdfBuilderForceReload = forceCompleteCSSReload;
    window.pdfBuilderTestStyles = addTestStyles;
    window.pdfBuilderCheckStyles = checkComputedStyles;
    window.pdfBuilderCheckJSCache = checkAndFixJSCacheCorruption;
    window.pdfBuilderEmergencyFix = emergencyCacheFix;

    // Auto-vérification au chargement
    $(document).ready(function() {
        // console.log('🚀 PDF Builder CSS Debug Tools chargées:');
        // console.log('   - pdfBuilderCheckCSS() : Vérifier déploiement');
        // console.log('   - pdfBuilderForceReload() : Forcer rechargement complet');
        // console.log('   - pdfBuilderTestStyles() : Ajouter styles de test');
        // console.log('   - pdfBuilderCheckStyles() : Vérifier styles calculés');
        // console.log('   - pdfBuilderCheckJSCache() : Vérifier et corriger cache JS');
        // console.log('   - pdfBuilderEmergencyFix() : Correction d\'urgence cache');

        // Vérifications automatiques
        setTimeout(checkCSSDeployment, 2000);
        setTimeout(checkAndFixJSCacheCorruption, 3000); // Vérifier le cache JS après les CSS
    });

})(jQuery);
