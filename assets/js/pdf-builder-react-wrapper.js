/**
 * PDF Builder React - Webpack Bundle Wrapper
 * Ensures the module is properly assigned to window when loaded
 * This file acts as the true webpack entry point that exports everything to window
 */

// Import the actual React module
import * as pdfBuilderReactModule from './pdf-builder-react/index.js';

// Extract the default export - it's already an object with all functions
const moduleExports = pdfBuilderReactModule.default || pdfBuilderReactModule;

// Export each property individually so webpack creates a plain object
export const initPDFBuilderReact = moduleExports.initPDFBuilderReact;
export const loadTemplate = moduleExports.loadTemplate;
export const getEditorState = moduleExports.getEditorState;
export const setEditorState = moduleExports.setEditorState;
export const getCurrentTemplate = moduleExports.getCurrentTemplate;
export const exportTemplate = moduleExports.exportTemplate;
export const saveTemplate = moduleExports.saveTemplate;
export const registerEditorInstance = moduleExports.registerEditorInstance;
export const resetAPI = moduleExports.resetAPI;
export const updateCanvasDimensions = moduleExports.updateCanvasDimensions;
export const _isWebpackBundle = true;

// Also signal when loaded
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
  try {
    // Assigner manuellement à window pour s'assurer que c'est disponible
    window.pdfBuilderReact = moduleExports;

    const event = new Event('pdfBuilderReactLoaded');
    document.dispatchEvent(event);
  } catch (e) {
    console.error('[pdf-builder-wrapper] Error dispatching event:', e);
  }
}

// Export default as well for compatibility
export default moduleExports;
