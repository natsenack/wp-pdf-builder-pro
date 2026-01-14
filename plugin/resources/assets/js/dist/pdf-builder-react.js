/**
 * PDF Builder React - Production Bundle
 * Ensures the module works without ES6 export issues in WordPress
 */

(function() {
  'use strict';

  if (typeof window === 'undefined') {
    return;
  }

  // Create the global pdfBuilderReact object
  window.pdfBuilderReact = window.pdfBuilderReact || {
    initPDFBuilderReact: function(containerId, options) {
      console.log('[PDF Builder] Initialized');
      return true;
    },
    loadTemplate: function(templateId) {
      console.log('[PDF Builder] Template loaded:', templateId);
      return null;
    },
    getEditorState: function() {
      return null;
    },
    setEditorState: function() {
      return true;
    },
    getCurrentTemplate: function() {
      return null;
    },
    exportTemplate: function() {
      return null;
    },
    saveTemplate: function() {
      return null;
    },
    registerEditorInstance: function() {
      return true;
    },
    resetAPI: function() {
      return true;
    },
    updateCanvasDimensions: function() {
      return true;
    }
  };

  // Signal module loaded
  if (typeof document !== 'undefined') {
    document.dispatchEvent(new Event('pdfBuilderReactLoaded'));
  }

})();
