// PDF Builder Pro - Script Loader
// Définit les variables globales immédiatement avant de charger le bundle webpack

console.error('🚀 PDF Builder Pro: Script Loader starting...');

// Définir les variables globales immédiatement
if (typeof window !== 'undefined') {
  console.error('🚀 PDF Builder Pro: Setting up global variables immediately in loader');

  // Créer l'API principale immédiatement
  const pdfBuilderPro = {
    version: '2.0.0',
    React: null, // Sera défini par le bundle webpack
    ReactDOM: null, // Sera défini par le bundle webpack
    editors: new Map(),

    init: function(containerId, options = {}) {
      console.log('PDF Builder Pro init called for', containerId, 'with options:', options);

      try {
        // Attendre que React soit disponible (chargé par le bundle webpack)
        if (!this.React || !this.ReactDOM) {
          console.warn('React not yet available, waiting...');
          setTimeout(() => this.init(containerId, options), 100);
          return false;
        }

        const container = document.getElementById(containerId);
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

  console.error('🚀 PDF Builder Pro: Global variables defined immediately in loader');
}

// Maintenant charger le bundle webpack qui contiendra React et les composants
console.error('🚀 PDF Builder Pro: Script Loader finished, loading webpack bundle...');