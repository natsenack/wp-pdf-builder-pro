/**
 * PDF Builder React - Webpack Bundle Wrapper
 * Ensures the module is properly assigned to window when loaded
 * This file acts as the true webpack entry point that exports everything to window
 */

console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: Wrapper script STARTED at ' + new Date().toISOString());

// The module is loaded as part of the entry
// const pdfBuilderReactModule = require('my-alias');

// Use the global
const pdfBuilderReactModule = window.pdfBuilderReact;

console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: window.pdfBuilderReact exists:', !!window.pdfBuilderReact);

// Extract the default export - it's already an object with all functions
const moduleExports = pdfBuilderReactModule;

console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: moduleExports:', moduleExports);

// Directly assign to window - no exports needed
if (typeof window !== 'undefined') {
  console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: Assigning to window...');

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

  console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: Assignment completed. window.pdfBuilderReact:', window.pdfBuilderReact);
  console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: window.pdfBuilderReactWrapper:', window.pdfBuilderReactWrapper);

  // Signal when loaded
  if (typeof document !== 'undefined') {
    try {
      console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: Dispatching pdfBuilderReactLoaded event...');
      const event = new Event('pdfBuilderReactLoaded');
      document.dispatchEvent(event);
      console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: Event dispatched successfully');
    } catch (e) {
      console.error('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: Error dispatching event:', e);
    }
  }

  console.log('🚀🚀🚀 PDF_BUILDER_WRAPPER_V4: Wrapper script COMPLETED at ' + new Date().toISOString());
}
