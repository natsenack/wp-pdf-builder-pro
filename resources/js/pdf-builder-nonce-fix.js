// PDF Builder Pro - Diagnostic des Scripts Chargés
// Version: 1.8 - 2025-10-25 - Diagnostic complet des scripts
// Ce fichier diagnostique tous les scripts chargés pour identifier la source de l'erreur

(function() {
    'use strict';

    // Intercepter les erreurs JavaScript globales
    var oldOnError = window.onerror;
    window.onerror = function(message, source, lineno, colno, error) {
        console.error('🚨 JAVASCRIPT ERROR DETECTED:', {
            message: message,
            source: source,
            line: lineno,
            column: colno,
            error: error,
            stack: error ? error.stack : 'No stack trace',
            timestamp: new Date().toISOString()
        });

        // Logger spécifiquement les erreurs de syntaxe
        if (message && message.indexOf("Unexpected token '?'") !== -1) {
            console.error('🚨 CRITICAL: Optional chaining syntax error detected!');
            console.error('This indicates a script is using modern JavaScript features not supported by the browser');

            // Essayer d'identifier le script problématique
            var scripts = document.getElementsByTagName('script');
            console.log('📋 All scripts currently loaded:', Array.from(scripts).map(function(s) {
                return {
                    src: s.src,
                    type: s.type,
                    innerHTML: s.innerHTML ? s.innerHTML.substring(0, 100) + '...' : null
                };
            }));

            // Vérifier les scripts externes récents
            var externalScripts = Array.from(scripts).filter(function(s) {
                return s.src && (s.src.indexOf('wp-content/plugins') !== -1 || s.src.indexOf('wp-content/themes') !== -1);
            });
            console.log('🔍 External scripts (plugins/themes):', externalScripts.map(function(s) {
                return s.src;
            }));
        }

        if (oldOnError) {
            return oldOnError(message, source, lineno, colno, error);
        }
        return false;
    };

    // Analyser les scripts au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 PDF Builder Pro: Analyzing loaded scripts...');

        var allScripts = document.getElementsByTagName('script');
        var scriptAnalysis = {
            total: allScripts.length,
            inline: 0,
            external: 0,
            plugins: [],
            themes: [],
            wordpress: [],
            external: []
        };

        Array.from(allScripts).forEach(function(script) {
            if (script.src) {
                scriptAnalysis.external++;

                if (script.src.indexOf('wp-content/plugins') !== -1) {
                    scriptAnalysis.plugins.push(script.src);
                } else if (script.src.indexOf('wp-content/themes') !== -1) {
                    scriptAnalysis.themes.push(script.src);
                } else if (script.src.indexOf('wp-admin') !== -1 || script.src.indexOf('wp-includes') !== -1) {
                    scriptAnalysis.wordpress.push(script.src);
                } else {
                    scriptAnalysis.external.push(script.src);
                }
            } else {
                scriptAnalysis.inline++;
            }
        });

        console.log('📊 Script Analysis Results:', scriptAnalysis);

        if (scriptAnalysis.plugins.length > 0) {
            console.warn('⚠️  Plugins with scripts:', scriptAnalysis.plugins);
        }

        if (scriptAnalysis.themes.length > 0) {
            console.warn('⚠️  Theme scripts:', scriptAnalysis.themes);
        }

        console.log('✅ PDF Builder Pro: Script analysis complete');
        console.log('💡 If you see "Unexpected token ?" errors, check the script sources above');
    });

})();
