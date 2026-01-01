/**
 * PDF Builder React - Webpack Bundle Wrapper
 * This file is the webpack entry point that will be assigned to pdfBuilderReact variable
 */

// Le module principal sera chargé séparément et assigné à window.pdfBuilderReact
// Ce wrapper sert juste de point d'entrée webpack

// Fonctions de fallback au cas où le module principal n'est pas encore chargé
const fallbackFunctions = {
  initPDFBuilderReact: () => {
    console.warn('PDF Builder React not yet loaded - initPDFBuilderReact called');
    return Promise.resolve();
  },
  loadTemplate: (templateData) => {
    console.warn('PDF Builder React not yet loaded - loadTemplate called', templateData);
    return Promise.resolve();
  },
  getEditorState: () => {
    console.warn('PDF Builder React not yet loaded - getEditorState called');
    return null;
  },
  setEditorState: (state) => {
    console.warn('PDF Builder React not yet loaded - setEditorState called', state);
  },
  getCurrentTemplate: () => {
    console.warn('PDF Builder React not yet loaded - getCurrentTemplate called');
    return null;
  },
  exportTemplate: () => {
    console.warn('PDF Builder React not yet loaded - exportTemplate called');
    return Promise.resolve(null);
  },
  saveTemplate: (template) => {
    console.warn('PDF Builder React not yet loaded - saveTemplate called', template);
    return Promise.resolve(null);
  },
  registerEditorInstance: (instance) => {
    console.warn('PDF Builder React not yet loaded - registerEditorInstance called', instance);
  },
  resetAPI: () => {
    console.warn('PDF Builder React not yet loaded - resetAPI called');
  },
  updateCanvasDimensions: (dimensions) => {
    console.warn('PDF Builder React not yet loaded - updateCanvasDimensions called', dimensions);
  },
  _isWebpackBundle: true
};

// Utiliser les fonctions du module principal si disponible, sinon les fallbacks
const getModuleExports = () => {
  if (typeof window !== 'undefined' && window.pdfBuilderReact) {
    return window.pdfBuilderReact;
  }
  return fallbackFunctions;
};

// Créer l'objet qui sera assigné par webpack
const pdfBuilderReact = {
  initPDFBuilderReact: (...args) => getModuleExports().initPDFBuilderReact(...args),
  loadTemplate: (...args) => getModuleExports().loadTemplate(...args),
  getEditorState: (...args) => getModuleExports().getEditorState(...args),
  setEditorState: (...args) => getModuleExports().setEditorState(...args),
  getCurrentTemplate: (...args) => getModuleExports().getCurrentTemplate(...args),
  exportTemplate: (...args) => getModuleExports().exportTemplate(...args),
  saveTemplate: (...args) => getModuleExports().saveTemplate(...args),
  registerEditorInstance: (...args) => getModuleExports().registerEditorInstance(...args),
  resetAPI: (...args) => getModuleExports().resetAPI(...args),
  updateCanvasDimensions: (...args) => getModuleExports().updateCanvasDimensions(...args),
  _isWebpackBundle: true
};

// Signal when loaded
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
  try {
    const event = new Event('pdfBuilderReactLoaded');
    document.dispatchEvent(event);
  } catch (e) {
    console.error('[pdf-builder-wrapper] Error dispatching event:', e);
  }
}

// Export pour webpack (sera assigné à la variable globale pdfBuilderReact)
module.exports = pdfBuilderReact;
