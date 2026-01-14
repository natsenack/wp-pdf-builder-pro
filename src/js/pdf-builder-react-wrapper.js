/**
 * PDF Builder React - Webpack Bundle Wrapper
 * Ensures the module is properly assigned to window when loaded
 * This file acts as the true webpack entry point that exports everything to window
 */

// Import the actual React module
import * as pdfBuilderReactModule from './pdf-builder-react/index.js';

// Extract the default export - it's already an object with all functions
const moduleExports = pdfBuilderReactModule.default || pdfBuilderReactModule;

// Directly assign to window - no exports needed
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = moduleExports;
  window.pdfBuilderReactWrapper = {
    initPDFBuilderReact: moduleExports.initPDFBuilderReact,
    loadTemplate: moduleExports.loadTemplate,
    getEditorState: moduleExports.getEditorState,
    setEditorState: moduleExports.setEditorState,
    getCurrentTemplate: moduleExports.getCurrentTemplate,
    exportTemplate: moduleExports.exportTemplate,
    saveTemplate: moduleExports.saveTemplate,
    registerEditorInstance: moduleExports.registerEditorInstance,
    resetAPI: moduleExports.resetAPI,
    updateCanvasDimensions: moduleExports.updateCanvasDimensions,
    _isWebpackBundle: true,
  };

  // Signal when loaded
  if (typeof document !== 'undefined') {
    try {
      const event = new Event('pdfBuilderReactLoaded');
      document.dispatchEvent(event);
    } catch (e) {
      console.error('[pdf-builder-wrapper] Error dispatching event:', e);
    }
  }
}
