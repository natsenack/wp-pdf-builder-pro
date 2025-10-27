// PDF Builder Pro - Diagnostic Simple
// Version: 2.0 - Diagnostic ultra-simple

(function() {
    'use strict';

    // Script chargé silencieusement

    // Analyser immédiatement les scripts
    setTimeout(function() {
        // Analyse silencieuse des scripts

        var scripts = document.getElementsByTagName('script');
        var pluginScripts = [];
        var themeScripts = [];

        for (var i = 0; i < scripts.length; i++) {
            var src = scripts[i].src;
            if (src) {
                if (src.indexOf('wp-content/plugins') !== -1) {
                    pluginScripts.push(src);
                } else if (src.indexOf('wp-content/themes') !== -1) {
                    themeScripts.push(src);
                }
            }
        }

        // Scripts analysés silencieusement
    }, 1000);

    // Intercepter les erreurs silencieusement
    var oldError = window.onerror;
    window.onerror = function(msg, url, line, col, error) {
        // Gestion silencieuse des erreurs
        if (oldError) {
            return oldError(msg, url, line, col, error);
        }
        return false;
    };

})();
