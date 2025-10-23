// PDF Builder Pro - Script Loader (Standalone)
// Définit les variables globales immédiatement - VERSION STANDALONE

(function() {
  'use strict';

  // Définir les variables globales immédiatement
  if (typeof window !== 'undefined') {

    // Créer l'API principale immédiatement
    var pdfBuilderPro = {
      version: '2.0.0',
      React: null, // Sera défini par le bundle webpack
      ReactDOM: null, // Sera défini par le bundle webpack
      editors: new Map(),

      init: function(containerId, options) {
        options = options || {};
        console.log('PDF Builder Pro init called for', containerId, 'with options:', options);

        try {
          // Attendre que React soit disponible (chargé par le bundle webpack)
          if (!this.React || !this.ReactDOM) {
            console.warn('React not yet available, waiting...');
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
      return pdfBuilderPro;
    };

  }

})();