/**
 * PDF Builder Pro - Force Complete CSS Reload
 * Script pour vérifier et forcer le rechargement complet des ressources CSS
 */

(function($) {
    'use strict';

    // Fonction pour vérifier si les fichiers CSS sont bien déployés
    function checkCSSDeployment() {
        console.log('🔍 PDF Builder: Vérification du déploiement CSS...');
        console.log('📋 pdfBuilderForceReload:', typeof pdfBuilderForceReload !== 'undefined' ? pdfBuilderForceReload : 'NON DÉFINI');

        // Vérifier les fichiers CSS attendus
        const cssFiles = [
            'contenu-settings.css',
            'modals-contenu.css'
        ];

        cssFiles.forEach(function(filename) {
            // TEMPORAIREMENT DÉSACTIVÉ - Problème de chemin URL
            console.log('🔍 Vérification CSS temporairement désactivée pour:', filename);
            return;

            // Utiliser l'URL localisée si disponible, sinon construire manuellement
            let baseUrl;
            if (typeof pdfBuilderForceReload !== 'undefined' && pdfBuilderForceReload.pluginUrl) {
                baseUrl = pdfBuilderForceReload.pluginUrl + 'resources/assets/css/';
                console.log('✅ Utilisation URL localisée pour', filename);
            } else {
                baseUrl = window.location.origin + '/wp-content/plugins/wp-pdf-builder-pro/plugin/resources/assets/css/';
                console.log('⚠️ Fallback URL manuelle pour', filename);
            }

            fetch(baseUrl + filename + '?_t=' + Date.now(), {
                method: 'HEAD',
                cache: 'no-cache'
            })
            .then(function(response) {
                console.log('🔗 Tentative de fetch:', baseUrl + filename + '?_t=' + Date.now());
                if (response.ok) {
                    console.log('✅ ' + filename + ' - déployé et accessible');
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
        console.log('🔄 PDF Builder: Forçage du rechargement COMPLET des CSS...');

        // Supprimer tous les liens CSS existants du plugin
        $('link[rel="stylesheet"]').each(function() {
            var href = $(this).attr('href');
            if (href && href.includes('wp-pdf-builder-pro')) {
                console.log('🗑️ Suppression du CSS:', href);
                $(this).remove();
            }
        });

        // Recharger la page complètement
        setTimeout(function() {
            console.log('🔄 Rechargement complet de la page...');
            window.location.reload(true);
        }, 1000);
    }

    // Fonction pour ajouter des styles inline temporaires pour tester
    function addTestStyles() {
        console.log('🎨 Ajout de styles de test temporaires...');

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

        console.log('✅ Styles de test ajoutés - les modales devraient avoir une bordure rouge pulsante');
    }

    // Fonction pour vérifier les styles calculés
    function checkComputedStyles() {
        console.log('🔍 Vérification des styles calculés...');

        // Attendre que le DOM soit prêt
        setTimeout(function() {
            $('.cache-modal-container').each(function(index) {
                var computed = window.getComputedStyle(this);
                console.log('📊 Container ' + (index + 1) + ' styles:', {
                    'border-radius': computed.getPropertyValue('border-radius'),
                    'box-shadow': computed.getPropertyValue('box-shadow'),
                    'max-width': computed.getPropertyValue('max-width'),
                    'background-color': computed.getPropertyValue('background-color')
                });
            });
        }, 1000);
    }

    // Exposer les fonctions globalement
    window.pdfBuilderCheckCSS = checkCSSDeployment;
    window.pdfBuilderForceReload = forceCompleteCSSReload;
    window.pdfBuilderTestStyles = addTestStyles;
    window.pdfBuilderCheckStyles = checkComputedStyles;

    // Auto-vérification au chargement
    $(document).ready(function() {
        console.log('🚀 PDF Builder CSS Debug Tools chargées:');
        console.log('   - pdfBuilderCheckCSS() : Vérifier déploiement');
        console.log('   - pdfBuilderForceReload() : Forcer rechargement complet');
        console.log('   - pdfBuilderTestStyles() : Ajouter styles de test');
        console.log('   - pdfBuilderCheckStyles() : Vérifier styles calculés');

        // Vérification automatique
        setTimeout(checkCSSDeployment, 2000);
    });

})(jQuery);