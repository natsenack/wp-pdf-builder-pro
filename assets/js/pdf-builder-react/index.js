// ============================================================================
// PDF Builder React Bundle - Entry Point
// ============================================================================

console.log('🔥 [PDF Builder] BUNDLE EXECUTING - START - TIMESTAMP:', new Date().toISOString());
console.log('🔥 [PDF Builder] Document ready state:', document.readyState);
console.log('🔥 [PDF Builder] Window location:', window.location.href);

// Import the main PDF Builder component
import PDFBuilder from '@/ts/components/PDFBuilder';

console.log('🔥 [PDF Builder] PDFBuilder import completed');

// Use WordPress globals instead of imports
var React = window.React;
var ReactDOM = window.ReactDOM;

console.log('🔥 [PDF Builder] React globals:', { React: typeof React, ReactDOM: typeof ReactDOM });
console.log('🔥 [PDF Builder] window.React version:', window.React && window.React.version);
console.log('🔥 [PDF Builder] window.ReactDOM version:', window.ReactDOM && window.ReactDOM.version);

// Flag pour afficher les logs d'initialisation détaillés
var DEBUG_VERBOSE = true;

if (DEBUG_VERBOSE) console.log('🚀 PDF Builder React bundle starting execution...');

function initPDFBuilderReact() {
  console.log('🔧 [PDF Builder] initPDFBuilderReact function called');
  console.log('📊 [PDF Builder] PDFBuilder type:', typeof PDFBuilder);
  console.log('📊 [PDF Builder] PDFBuilder:', PDFBuilder);

  try {
    console.log('🔍 [PDF Builder] Looking for container...');
    // Vérifier si le container existe
    var container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 [PDF Builder] Container element:', container);
    if (!container) {
      console.error('❌ [PDF Builder] Container #pdf-builder-react-root not found');
      return false;
    }

    console.log('✅ [PDF Builder] Container found, checking dependencies...');

    // Vérifier les dépendances
    console.log('🔧 [PDF Builder] Checking React availability:', typeof React, React);
    if (typeof React === 'undefined') {
      console.error('❌ [PDF Builder] React is not available');
      return false;
    }
    console.log('🔧 [PDF Builder] Checking ReactDOM availability:', typeof ReactDOM, ReactDOM);
    if (typeof ReactDOM === 'undefined') {
      console.error('❌ [PDF Builder] ReactDOM is not available');
      return false;
    }
    console.log('🔧 [PDF Builder] Checking ReactDOM.createRoot:', typeof ReactDOM.createRoot);
    if (typeof ReactDOM.createRoot === 'undefined') {
      console.error('❌ [PDF Builder] ReactDOM.createRoot is not available');
      return false;
    }

    console.log('✅ [PDF Builder] React dependencies available');

    console.log('🎯 [PDF Builder] All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');

    console.log('🎨 [PDF Builder] Hiding loading, showing editor:', { loadingEl, editorEl });
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';

    console.log('🎨 [PDF Builder] Creating React root...');

    // Créer et rendre l'application React
    var root = ReactDOM.createRoot(container);
    console.log('🎨 [PDF Builder] React root created, rendering PDF Builder component...');

    console.log('📦 [PDF Builder] About to create element from PDFBuilder...');
    var pdfBuilderElement = React.createElement(PDFBuilder);

    console.log('🎨 [PDF Builder] Created PDF Builder element:', pdfBuilderElement);
    console.log('🎨 [PDF Builder] Element type:', pdfBuilderElement.type);
    console.log('🎨 [PDF Builder] Element props:', pdfBuilderElement.props);

    console.log('🔴 [PDF Builder] ABOUT TO RENDER TO CONTAINER');
    root.render(pdfBuilderElement);
    console.log('✅ [PDF Builder] PDF Builder component rendered successfully');

    return true;

  } catch (error) {
    console.error('❌ [PDF Builder] Error in initPDFBuilderReact:', error);
    console.error('❌ [PDF Builder] Error stack:', error.stack);
    console.error('❌ [PDF Builder] Error message:', error.message);
    var container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}

if (DEBUG_VERBOSE) console.log('📦 Creating exports object...');

// Export default pour webpack
var exports = {
  initPDFBuilderReact
};

if (DEBUG_VERBOSE) console.log('🌐 Assigning to window...');

// Wrapper IIFE for immediate execution
(function() {
  console.log('🔄 [PDF Builder] IIFE starting at', new Date().toISOString());
  if (typeof window === 'undefined') {
    console.warn('⚠️ [PDF Builder] Window not available, skipping global assignment');
    return;
  }

  console.log('🔄 [PDF Builder] About to assign to window.pdfBuilderReact');
  console.log('🔄 [PDF Builder] exports object:', exports);
  console.log('🔄 [PDF Builder] exports.initPDFBuilderReact:', typeof exports.initPDFBuilderReact);

  // CRITICAL: Assign the exports object directly and immediately
  window.pdfBuilderReact = exports;
  console.log('🌐 [PDF Builder] Assigned to window.pdfBuilderReact:', window.pdfBuilderReact);
  console.log('🌐 [PDF Builder] window.pdfBuilderReact keys:', Object.keys(window.pdfBuilderReact));
  
  // Verify immediately
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    console.log('✅ [PDF Builder] initPDFBuilderReact function is available globally');
    console.log('✅ [PDF Builder] initPDFBuilderReact:', window.pdfBuilderReact.initPDFBuilderReact);
  } else {
    console.error('❌ [PDF Builder] initPDFBuilderReact function NOT available globally');
    console.error('❌ [PDF Builder] window.pdfBuilderReact:', window.pdfBuilderReact);
    console.error('❌ [PDF Builder] typeof window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  }
}).call(window);

if (DEBUG_VERBOSE) console.log('🎉 PDF Builder React bundle execution completed at', new Date().toISOString());
