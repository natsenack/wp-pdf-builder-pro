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

        try {
          // Attendre que React soit disponible (chargé par le bundle webpack)
          if (!this.React || !this.ReactDOM) {
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
          return false;
        }
      },

      destroy: function(containerId) {
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

    console.log('🔧 PDF Builder Pro: Script-loader chargé avec succès');
    console.log('🔧 API disponible:', typeof window.pdfBuilderPro.init);

  }

})();