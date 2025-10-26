// PDF Builder Pro - Script Loader (Standalone)
// Définit les variables globales immédiatement - VERSION SIMPLIFIÉE SANS WEBPACK

console.log('🚨🚨🚨 SCRIPT-LOADER.JS CHARGÉ ET EXÉCUTÉ - VERSION FINALE 🚨🚨🚨');

(function() {
  'use strict';

  // Définir les variables globales immédiatement
  if (typeof window !== 'undefined') {
    // Créer l'API principale immédiatement
    var pdfBuilderPro = {
      version: '4.0.0-final',
      React: window.React,
      ReactDOM: window.ReactDOM,
      editors: new Map(),

      init: function(containerId, options) {
        console.log('SCRIPT-LOADER: init() appelée avec', { containerId: containerId, options: options });
        options = options || {};

        try {
          console.log('SCRIPT-LOADER: Initialisation (React bundlé)...');
          var container = document.getElementById(containerId);
          if (!container) {
            throw new Error('Container element \'' + containerId + '\' not found');
          }

          // Afficher un message temporaire en attendant React
          container.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Chargement de l\'éditeur PDF React...</div>';

          // Déléguer à l'initialisation React (qui sera chargée par le main bundle)
          if (window.pdfBuilderInitReact) {
            console.log('SCRIPT-LOADER: Délégation à pdfBuilderInitReact');
            return window.pdfBuilderInitReact(containerId, options);
          } else {
            console.log('SCRIPT-LOADER: pdfBuilderInitReact pas encore disponible, attente...');
            // Attendre que le main bundle charge
            var checkReactInit = function() {
              if (window.pdfBuilderInitReact) {
                console.log('SCRIPT-LOADER: pdfBuilderInitReact maintenant disponible');
                return window.pdfBuilderInitReact(containerId, options);
              } else {
                setTimeout(checkReactInit, 50);
              }
            };
            setTimeout(checkReactInit, 50);
          }

          return true;

        } catch (error) {
          console.error('SCRIPT-LOADER: Erreur dans init:', error);
          return false;
        }
      },

      destroy: function(containerId) {
        console.log('SCRIPT-LOADER: destroy() appelée pour:', containerId);
        if (window.pdfBuilderDestroyReact) {
          return window.pdfBuilderDestroyReact(containerId);
        }
        return false;
      },

      getData: function(containerId) {
        if (window.pdfBuilderGetDataReact) {
          return window.pdfBuilderGetDataReact(containerId);
        }
        return null;
      },

      getElements: function() {
        if (window.pdfBuilderGetElementsReact) {
          return window.pdfBuilderGetElementsReact();
        }
        return [];
      }
    };

    // Définir les variables globales
    window.pdfBuilderPro = pdfBuilderPro;
    window.PDFBuilderPro = pdfBuilderPro;
    window.initializePDFBuilderPro = function() {
      return pdfBuilderPro;
    };

    console.log('🔧 PDF Builder Pro: Script-loader chargé avec succès (version finale)');
    console.log('🔧 API disponible:', typeof window.pdfBuilderPro.init);
  }
})();