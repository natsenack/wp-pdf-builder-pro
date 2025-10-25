// PDF Builder Pro - Bundle Minimal pour Compatibilité ES5
// Version simplifiée sans composants React complexes

(function() {
  'use strict';

  try {
    // Créer l'objet global s'il n'existe pas
    if (typeof window !== 'undefined') {
      if (!window.pdfBuilderPro) {
        window.pdfBuilderPro = {};
      }

      // Méthode init simplifiée
      window.pdfBuilderPro.init = function(containerId, options) {
        console.log('PDF Builder Pro: Version minimale compatible ES5 chargée', { containerId: containerId, options: options });

        // Essayer de monter un composant minimal
        var container = document.getElementById(containerId);
        if (container) {
          container.innerHTML =
            '<div style=\"padding: 20px; text-align: center; border: 1px solid #ddd; border-radius: 8px;\">' +
              '<h3>PDF Builder Pro</h3>' +
              '<p>Version de compatibilité ES5 chargée.</p>' +
              '<p>Container: ' + containerId + '</p>' +
              '<button>Test</button>' +
            '</div>';
          console.log('PDF Builder Pro: Container initialisé avec version minimale');
        } else {
          console.error('PDF Builder Pro: Container non trouvé', containerId);
        }
      };

      console.log('PDF Builder Pro safe bundle loaded successfully');
    }

  } catch (error) {
    console.error('PDF Builder Pro: Erreur lors du chargement du bundle minimal', error);

    // Fallback ultime
    if (typeof window !== 'undefined') {
      if (!window.pdfBuilderPro) {
        window.pdfBuilderPro = {};
      }
      window.pdfBuilderPro.init = function(containerId) {
        console.log('PDF Builder Pro: Fallback ultime activé', containerId);
        var container = document.getElementById(containerId);
        if (container) {
          container.innerHTML = '<p>PDF Builder Pro: Mode de compatibilité basique</p>';
        }
      };
    }
  }
})();
