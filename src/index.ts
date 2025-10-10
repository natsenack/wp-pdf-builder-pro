// Simple JavaScript pour commencer
(function() {
  'use strict';

  var PDFBuilderPro = {
    version: '1.0.0',
    init: function(containerId) {
    }
  };

  // Attacher à window pour WordPress
  if (typeof window !== 'undefined') {
    window.PDFBuilderPro = PDFBuilderPro;
  }

  // Export pour les modules
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = PDFBuilderPro;
  }
})();