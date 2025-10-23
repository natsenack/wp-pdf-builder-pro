// PDF Builder Pro - Standalone Script Loader
// Définit les variables globales immédiatement - VERSION COMPLÈTEMENT STANDALONE

(function() {
  'use strict';

  console.error('🚀 PDF Builder Pro: Standalone Script Loader starting...');

  // Définir les variables globales immédiatement
  if (typeof window !== 'undefined') {
    console.error('🚀 PDF Builder Pro: Setting up global variables immediately in standalone loader');

    // Créer l'API principale immédiatement
    var pdfBuilderPro = {
      version: '2.0.0',
      React: null, // Sera défini par le bundle webpack
      ReactDOM: null, // Sera défini par le bundle webpack
      editors: new Map(),

      init: function(containerId, options) {
        options = options || {};
        console.log('PDF Builder Pro init called for', containerId, 'with options:', options);

        // Compteur pour éviter la boucle infinie
        if (!options._retryCount) {
          options._retryCount = 0;
        }
        options._retryCount++;

        try {
          // Attendre que React soit disponible (chargé par le bundle webpack)
          if (!this.React || !this.ReactDOM) {
            if (options._retryCount > 50) {
              console.error('PDF Builder Pro: React not available after 50 retries, giving up');
              return false;
            }
            console.warn('React not yet available, waiting... (attempt ' + options._retryCount + '/50)');
            var self = this;
            setTimeout(function() { self.init(containerId, options); }, 100);
            return false;
          }

          var container = document.getElementById(containerId);
          if (!container) {
            throw new Error('Container element \'' + containerId + '\' not found');
          }

          // Afficher un message temporaire en attendant que le bundle principal charge les composants
          container.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Chargement de l\'éditeur PDF...</div>';

          console.error('🚀 PDF Builder Pro: React app initialization deferred to main bundle');
          return true;

        } catch (error) {
          console.error('PDF Builder Pro init failed:', error);
          return false;
        }
      },

      destroy: function(containerId) {
        console.log('PDF Builder Pro destroy called for', containerId);
      },

      getData: function(containerId) {
        return null;
      },

      getElements: function() {
        return [];
      }
    };

    // Définir les variables globales immédiatement
    window.pdfBuilderPro = pdfBuilderPro;
    window.PDFBuilderPro = pdfBuilderPro; // Alias avec majuscule pour compatibilité
    window.initializePDFBuilderPro = function() {
      console.error('🚀 PDF Builder Pro: initializePDFBuilderPro called');
      return pdfBuilderPro;
    };

    console.error('🚀 PDF Builder Pro: Global variables defined immediately in standalone loader');
  }

  console.error('🚀 PDF Builder Pro: Standalone Script Loader finished');
})();