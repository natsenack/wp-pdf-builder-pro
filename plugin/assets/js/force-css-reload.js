/**
 * PDF Builder Pro - Force CSS Reload
 * Script pour forcer le rechargement des ressources CSS en cas de cache persistant
 */

(function($) {
    'use strict';

    // Fonction pour forcer le rechargement des CSS
    function forceCSSReload() {
        console.log('🔄 PDF Builder: Forçage du rechargement CSS...');

        // Récupérer tous les liens CSS du plugin
        $('link[rel="stylesheet"]').each(function() {
            var href = $(this).attr('href');
            if (href && href.indexOf('wp-pdf-builder-pro') !== -1) {
                // Ajouter un paramètre unique pour forcer le rechargement
                var newHref = href + (href.indexOf('?') !== -1 ? '&' : '?') + '_force=' + Date.now();
                $(this).attr('href', newHref);
                console.log('📄 CSS rechargé:', href);
            }
        });

        console.log('✅ PDF Builder: Rechargement CSS terminé');
    }

    // Exposer la fonction globalement pour utilisation manuelle
    window.pdfBuilderForceCSSReload = forceCSSReload;

    // Auto-rechargement au chargement de la page (optionnel)
    // $(document).ready(function() {
    //     setTimeout(forceCSSReload, 1000);
    // });

})(jQuery);