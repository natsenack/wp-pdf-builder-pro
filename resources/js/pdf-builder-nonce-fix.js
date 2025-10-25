// PDF Builder Pro - Diagnostic Simple
// Version: 2.0 - Diagnostic ultra-simple

(function() {
    'use strict';

    console.log('� PDF BUILDER DIAGNOSTIC: Script loaded successfully');

    // Analyser immédiatement les scripts
    setTimeout(function() {
        console.log('� ANALYZING SCRIPTS...');

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

        console.log('🔍 PLUGINS SCRIPTS FOUND:', pluginScripts.length);
        pluginScripts.forEach(function(src, index) {
            console.log('  ' + (index + 1) + '. ' + src);
        });

        console.log('🎨 THEME SCRIPTS FOUND:', themeScripts.length);
        themeScripts.forEach(function(src, index) {
            console.log('  ' + (index + 1) + '. ' + src);
        });

        console.log('✅ PDF BUILDER DIAGNOSTIC: Analysis complete');
    }, 1000);

    // Intercepter les erreurs
    var oldError = window.onerror;
    window.onerror = function(msg, url, line, col, error) {
        if (msg && msg.indexOf("Unexpected token '?'") !== -1) {
            console.error('� FOUND THE ERROR! Source:', url, 'Line:', line);
            console.error('🚨 This script contains optional chaining operators');
        }
        if (oldError) {
            return oldError(msg, url, line, col, error);
        }
        return false;
    };

})();
